<?php

namespace Inviqa\StashRedisDriverBundle;

use Inviqa\StashRedisDriverBundle\Driver\Predis;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Stash\DriverList;

/**
 * Class StashRedisDriverBundle
 *
 * @package Inviqa\StashRedisDriverBundle
 */
class StashRedisDriverBundle extends Bundle
{
    /**
     * StashRedisDriverBundle constructor.
     */
    public function __construct()
    {
        // Register custom Redis driver. We are registering it with the same
        // name, overriding Stash's driver, to get the same configuration.
        DriverList::registerDriver('Redis', Predis::class);
    }
}
