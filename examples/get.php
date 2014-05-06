<?php
/**
 * GET request example
 * @author alxmsl
 * @date 1/13/13
 */

// Firstly include base class
include('../source/Autoloader.php');

use Network\Http\Request;

// Create request object
$Request = new Request();
$Request->setUrl('http://topface.com')
    ->setConnectTimeout(3)
    ->setTimeout(5);

// Setup transport
$Request->setTransport(Request::TRANSPORT_CURL);

// Send request
$data = $Request->send();
var_dump($data);