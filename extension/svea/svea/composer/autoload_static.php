<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit358c631fcbe21e22fbb6aba481fe515e
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Svea\\WebPay\\' => 12,
            'Svea\\Checkout\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Svea\\WebPay\\' => 
        array (
            0 => __DIR__ . '/..' . '/sveaekonomi/webpay/src',
        ),
        'Svea\\Checkout\\' => 
        array (
            0 => __DIR__ . '/..' . '/sveaekonomi/checkout/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit358c631fcbe21e22fbb6aba481fe515e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit358c631fcbe21e22fbb6aba481fe515e::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
