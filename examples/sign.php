<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 *
 * Request signification example
 * @author alxmsl
 * @date 3/30/13
 */

// Firstly include base class
include('../source/Autoloader.php');

use alxmsl\Network\Http\Request;

// Create request object
$Request = new Request();
$Request->setUrl('http://topface.com');

// Setup transport
$Request->setTransport(Request::TRANSPORT_CURL);

// Add request data
$Request->addGetField('a', 'value_a');
$Request->addGetField('b', 'value_b');

$Request->addPostField('c', 'value_c');
$Request->addPostField('d', 'value_d');

// Create custom request data signature
$sign = $Request->getSignature(function (Request $Request) {
    $sign = '';
    $get = $Request->getGetData();
    if (!empty($get)) {
        $sign = http_build_query($get);
    }
    $post = $Request->getPostData();
    if (!empty($post)) {
        $sign .= '&' . http_build_query($post);
    }
    return $sign;
});
var_dump($sign);

$Request->addPostField('sign', $sign);