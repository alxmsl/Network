<?php
/**
 * Request example with url parametrization
 * @author alxmsl
 * @date 2/6/13
 */

// Firstly include base class
include('../source/Autoloader.php');

use Network\Http\Request;

// Create request object
$Request = new Request();
$Request->setUrl('https://www.googleapis.com/androidpublisher/v1/')
    ->setConnectTimeout(3)
    ->setTimeout(5);

// Setup transport
$Request->setTransport(Request::TRANSPORT_CURL);

// Set url data fields
$Request->addUrlField('applications', 'com.my.application')
    ->addUrlField('subscriptions', 'com.my.application.subscription.1')
    ->addUrlField('token', 'some access token');

// Send request
$data = $Request->send();
var_dump($data);