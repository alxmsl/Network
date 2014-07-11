<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 *
 * Request example with url parametrization
 * @author alxmsl
 * @date 2/6/13
 */

// Firstly include base class
include('../source/Autoloader.php');

use alxmsl\Network\Http\Request;

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