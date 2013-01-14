<?php

namespace Network;

// append Cli autoloader
spl_autoload_register(array('\Network\Autoloader', 'Autoloader'));

/**
 *
 * @author alxmsl
 * @date 1/13/13
 */ 
final class Autoloader {
    /**
     * @var array array of available classes
     */
    private static $classes = array(
        'Network\\Autoloader'          => 'Autoloader.php',
        'Network\\Http\\Request'       => 'Request.php',
        'Network\\RequestInterface'    => 'RequestInterface.php',
        'Network\\Http\\CurlTransport' => 'CurlTransport.php',
        'Network\\TransportInterface'  => 'TransportInterface.php',
    );

    /**
     * Component autoloader
     * @param string $className claass name
     */
    public static function Autoloader($className) {
        if (array_key_exists($className, self::$classes)) {
            $fileName = realpath(dirname(__FILE__)) . '/' . self::$classes[$className];
            if (file_exists($fileName)) {
                include $fileName;
            }
        }
    }
}
