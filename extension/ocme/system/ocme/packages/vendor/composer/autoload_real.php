<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit4ebdd63183bac631f231658d241b83ee
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoaderOcme' === $class) {
            require __DIR__ . '/ClassLoaderOcme.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit4ebdd63183bac631f231658d241b83ee', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoaderOcme(\dirname(\dirname(__FILE__)));
        spl_autoload_unregister(array('ComposerAutoloaderInit4ebdd63183bac631f231658d241b83ee', 'loadClassLoader'));

        $useStaticLoader = PHP_VERSION_ID >= 50600 && !defined('HHVM_VERSION') && (!function_exists('zend_loader_file_encoded') || !zend_loader_file_encoded());
        if ($useStaticLoader) {
            require __DIR__ . '/autoload_static.php';

            call_user_func(\Composer\Autoload\ComposerStaticInit4ebdd63183bac631f231658d241b83ee::getInitializer($loader));
        } else {
            $map = require __DIR__ . '/autoload_namespaces.php';
            foreach ($map as $namespace => $path) {
                $loader->set($namespace, $path);
            }

            $map = require __DIR__ . '/autoload_psr4.php';
            foreach ($map as $namespace => $path) {
                $loader->setPsr4($namespace, $path);
            }

            $classMap = require __DIR__ . '/autoload_classmap.php';
            if ($classMap) {
                $loader->addClassMap($classMap);
            }
        }

        $loader->register(true);

        if ($useStaticLoader) {
            $includeFiles = Composer\Autoload\ComposerStaticInit4ebdd63183bac631f231658d241b83ee::$files;
        } else {
            $includeFiles = require __DIR__ . '/autoload_files.php';
        }
        foreach ($includeFiles as $fileIdentifier => $file) {
            composerRequire4ebdd63183bac631f231658d241b83ee($fileIdentifier, $file);
        }

        return $loader;
    }
}

/**
 * @param string $fileIdentifier
 * @param string $file
 * @return void
 */
function composerRequire4ebdd63183bac631f231658d241b83ee($fileIdentifier, $file)
{
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;

        require $file;
    }
}
