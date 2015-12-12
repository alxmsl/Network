Network
=======

Native network requests driver for PHP

Installation
-------

Require packet in a composer.json

    "alxmsl/network": ">=1.0.0"

Run Composer: `php composer.phar install`

Usage example
-------

    // Firstly include base class
    include('../source/Autoloader.php');

    use Network\Http\Request;

    // Create request object
    $Request = new Request();
    $Request->setUrl('http://topface.com')
        ->setConnectTimeout(3)
        ->setTimeout(5);

Now you need to select transport type.
Today it supports only curl transport type, but if future will other types. For example, sockets

    $Request->setTransport(Request::TRANSPORT_CURL);

Using curl transport, you can add any additional [curl options](http://php.net/manual/en/function.curl-setopt.php)

    $Request->getTransport()->setOption(CURLOPT_FOLLOWLOCATION, true);

You can add url parameters for requests like a http://some.body/param1/value1/param2/value2

    $Request->addUrlField('param1', 'value1')
        ->addUrlField('param2', 'value2');

You can add GET parameters

    $Request->addGetField('param3', 'value3')
        ->addGetField('param4', 'value4');

You can add POST parameters

    $Request->addPostField('field5', 'value5');
    $Request->addPostField('field6', 'value6');

And send the request

    $data = $Request->send();

License
-------
Copyright © 2014 Alexey Maslov <alexey.y.maslov@gmail.com>
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
