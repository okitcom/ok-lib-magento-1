<?php

namespace OK\Credentials\Environment;


class LocalEnvironment extends Environment
{
    /**
     * Environment path part
     * @return string
     */
    function getBaseUrl() {
        return "local.okit.io";
    }
}