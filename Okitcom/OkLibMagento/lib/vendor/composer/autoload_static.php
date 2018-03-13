<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6504896b0421ce0a441363d883e9d214
{
    public static $prefixLengthsPsr4 = array (
        'O' => 
        array (
            'OK\\' => 3,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'OK\\' => 
        array (
            0 => __DIR__ . '/..' . '/okitcom/ok-lib-php/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6504896b0421ce0a441363d883e9d214::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6504896b0421ce0a441363d883e9d214::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
