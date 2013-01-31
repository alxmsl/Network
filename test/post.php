<?php
/**
 * POST request example
 * @author alxmsl
 * @date 1/25/13
 */

// Firstly include base class
include('../source/Autoloader.php');

use Network\Http\Request;

// Create request object
$Request = new Request();
$Request->setUrl('http://api.topface.com')
    ->setConnectTimeout(3)
    ->setTimeout(5);

// Setup transport
$Request->setTransport(Request::TRANSPORT_CURL);

// Set POST data fields
$Request->addPostField('field1', 'value1');
$Request->addPostField('field2', 'value2');

// Send request
$data = $Request->send();
var_dump($data);