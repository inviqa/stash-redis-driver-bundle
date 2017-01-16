<?php

namespace Inviqa\StashDriver;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class StashRedisDriverBundle
 *
 * @package Inviqa\StashDriver
 */
class StashRedisDriverBundle extends Bundle
{

}


namespace InviqaOverridesBundle;

use InviqaOverridesBundle\Stash\Predis;
use Stash\DriverList;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class InviqaOverridesBundle
 *
 * @package InviqaOverridesBundle
 */
class InviqaOverridesBundle extends Bundle
{

    /**
     * InviqaOverridesBundle constructor.
     */
    public function __construct()
    {
        // Register custom Redis driver. We are registering it with the same
        // name, overriding Stash's driver, to get the same configuration.
        DriverList::registerDriver('Redis', Predis::class);
    }
}

