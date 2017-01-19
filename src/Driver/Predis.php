<?php

namespace Inviqa\StashRedisDriverBundle\Driver;

use Predis\Client;
use Stash;

/**
 * Class Predis
 *
 * This class was originally developed by Samuel Roze <sroze@inviqa.com> for the FT project. The original version was
 * locked to specific Predis version, which didn't allow updating the eZ Platform, so new version had to be created.
 *
 * Most of the functionality of the class remains the same, though.
 *
 * List of changes done by David Lukac <dlukac@inviqa.com>:
 * - add `isPersistent` function as per interface requirements.
 * - adjust implementation of `getRedisClient` function due to change of how the $options array is injected.
 *
 * @todo Create independent bundle, identify Predis version to tie to.
 *
 * @package Inviqa\StashRedisDriverBundle\Driver
 */
class Predis extends Stash\Driver\AbstractDriver
{
    /**
     * @var array
     */
    private $options = [];

    /**
     * @var Client|null
     */
    private $client;

    /**
     * @var array
     */
    private $keyCache = [];

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        if (null !== $this->client) {
            $this->client->disconnect();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getData($key)
    {
        return unserialize($this->getRedisClient()->get($this->makeKeyString($key)));
    }

    /**
     * {@inheritdoc}
     */
    public function storeData($key, $data, $expiration)
    {
        $store = serialize(array('data' => $data, 'expiration' => $expiration));
        $ttl = $expiration !== null ? $expiration - time() : null;

        if ($ttl !== null && $ttl < 1) {
            // Prevent us from even passing a negative ttl'd item to redis,
            // since it will just round up to zero and cache forever.
            return true;
        }

        return $this->getRedisClient()->set($this->makeKeyString($key), $store, 'ex', $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function clear($key = null)
    {
        $redis = $this->getRedisClient();
        if (null === $key) {
            $redis->flushdb();

            return true;
        }

        $path = $this->makeKeyString($key, true);
        $key = $this->makeKeyString($key);

        $redis->del($key);
        $redis->incr($path);

        $this->keyCache = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function isAvailable()
    {
        return class_exists(Client::class);
    }

    /**
     * @return Client
     */
    private function getRedisClient()
    {
        if (null === $this->client) {
            // Convert loaded configuration into array of connection string.
            $parameters = array_map(function (array $serverConfiguration) {
                return "tcp://{$serverConfiguration['server']}:{$serverConfiguration['port']}";
            }, $this->options['servers']);

            $replication = (bool) count($parameters) > 1;

            if (false === $replication) {
                // If we are not using multiple servers, pass only the connection
                // string, otherwise we're getting:
                // Warning: call_user_func_array() expects parameter 1 to be a valid callback, no array or string given
                $parameters = reset($parameters);
            }

            $options = [
                'replication' => $replication,
            ];

            $this->client = new Client($parameters, $options);
        }

        return $this->client;
    }

    /**
     * Turns a key array into a key string.
     *
     * *Note:* This key logic come from the original Stash Redis driver.
     *
     * @see https://github.com/tedious/Stash/blob/master/src/Stash/Driver/Redis.php
     *
     * @param array $keys
     * @param bool $path
     *
     * @return string
     */
    protected function makeKeyString($keys, $path = false)
    {
        $keys = Stash\Utilities::normalizeKeys($keys);

        $keyString = 'cache:::';
        $pathKey = ':pathdb::';

        foreach ($keys as $name) {
            //a. cache:::name
            //b. cache:::name0:::sub
            $keyString .= $name;

            //a. :pathdb::cache:::name
            //b. :pathdb::cache:::name0:::sub
            $pathKey = ':pathdb::' . $keyString;
            $pathKey = md5($pathKey);

            if (isset($this->keyCache[$pathKey])) {
                $index = $this->keyCache[$pathKey];
            } else {
                $index = $this->getRedisClient()->get($pathKey);
                $this->keyCache[$pathKey] = $index;
            }

            //a. cache:::name0:::
            //b. cache:::name0:::sub1:::
            $keyString .= '_' . $index . ':::';
        }

        return $path ? $pathKey : md5($keyString);
    }

    /**
     * @inheritdoc
     */
    public function isPersistent()
    {
        return true;
    }
}
