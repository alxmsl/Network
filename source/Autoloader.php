<?php
/*
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */

namespace alxmsl\Network;

// append Cli autoloader
spl_autoload_register(array('\alxmsl\Network\Autoloader', 'autoload'));

/**
 * Base autoloader class
 * @author alxmsl
 * @date 1/13/13
 */ 
final class Autoloader {
    /**
     * @var array array of available classes
     */
    private static $classes = array(
        'alxmsl\\Network\\Autoloader'          => 'Autoloader.php',
        'alxmsl\\Network\\RequestInterface'    => 'RequestInterface.php',
        'alxmsl\\Network\\TransportInterface'  => 'TransportInterface.php',

        'alxmsl\\Network\\Http\\Request'       => 'Http/Request.php',
        'alxmsl\\Network\\Http\\CurlTransport' => 'Http/CurlTransport.php',

        'alxmsl\\Network\\Exception\\HttpException'                  => 'Exception/HttpException.php',
        'alxmsl\\Network\\Exception\\HttpClientErrorCodeException'   => 'Exception/HttpClientErrorCodeException.php',
        'alxmsl\\Network\\Exception\\HttpCodeException'              => 'Exception/HttpCodeException.php',
        'alxmsl\\Network\\Exception\\HttpContentTypeException'       => 'Exception/HttpContentTypeException.php',
        'alxmsl\\Network\\Exception\\HttpInformationalCodeException' => 'Exception/HttpInformationalCodeException.php',
        'alxmsl\\Network\\Exception\\HttpRedirectionCodeException'   => 'Exception/HttpRedirectionCodeException.php',
        'alxmsl\\Network\\Exception\\HttpServerErrorCodeException'   => 'Exception/HttpServerErrorCodeException.php',

        'alxmsl\\Network\\Exception\\TransportException' => 'Exception/TransportException.php',
        'alxmsl\\Network\\Exception\\CurlErrorException' => 'Exception/CurlErrorException.php',
    );

    /**
     * Component autoloader
     * @param string $className claass name
     */
    public static function autoload($className) {
        if (array_key_exists($className, self::$classes)) {
            $fileName = realpath(dirname(__FILE__)) . '/' . self::$classes[$className];
            if (file_exists($fileName)) {
                include $fileName;
            }
        }
    }
}
