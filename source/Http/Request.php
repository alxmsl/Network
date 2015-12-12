<?php
/*
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */

namespace alxmsl\Network\Http;
use alxmsl\Network\Exception\TransportException;
use alxmsl\Network\RequestInterface;
use alxmsl\Network\TransportInterface;
use Closure;
use InvalidArgumentException;
use LogicException;

/**
 * Class for Http request
 * @author alxmsl
 * @date 1/13/13
 */ 
final class Request implements RequestInterface {
    /**
     * Http request methods
     */
    const METHOD_AUTO = 0, // Setup POST, if POST data was present
          METHOD_GET  = 1, // Strictly GET request
          METHOD_POST = 2; // Strictly POST request

    /**
     * Content type constants
     */
    const CONTENT_TYPE_UNDEFINED = 0,
          CONTENT_TYPE_JSON      = 1,
          CONTENT_TYPE_TEXT      = 2,
          CONTENT_TYPE_XML       = 3;

    /**
     * Transport library constants
     */
    const TRANSPORT_CURL = 0;

    /**
     * @var TransportInterface transport implementation object
     */
    private $Transport = null;

    /**
     * @var string URL string value
     */
    private $url = '';

    /**
     * @var array request headers
     */
    private $headers = [];

    /**
     * @var int connect timeout for the request, seconds
     */
    private $connectTimeout = 1;

    /**
     * @var int request timeout for the request, seconds
     */
    private $timeout = 1;

    /**
     * @var int content type for the request
     */
    private $contentTypeCode = self::CONTENT_TYPE_UNDEFINED;

    /**
     * @var int HTTP method type
     */
    private $method = self::METHOD_AUTO;

    /**
     * @var array GET parameters
     */
    private $getData = [];

    /**
     * @var array|null POST parameters
     */
    private $postData = null;

    /**
     * @var array url parameters
     */
    private $urlData = [];

    /**
     * @var int SSL version code for request
     */
    private $sslVersion = 0;

    /**
     * Send request data method
     * @return string request execution result
     * @throws TransportException in case of undefined Transport
     */
    public function send() {
        if (is_null($this->Transport)) {
            throw new TransportException();
        }
        $this->Transport->setRequest($this);
        return $this->Transport->makeHttpRequest($this);
    }

    /**
     * Getter for response headers data
     * @return array response headers data
     */
    public function getResponseHeaders() {
        return $this->Transport->getResponseHeaders();
    }

    /**
     * Signature getter
     * @param callable $Signification signification callback function
     * @return string signature value
     */
    public function getSignature(Closure $Signification) {
        return (string) $Signification($this);
    }

    /**
     * Content type setter
     * @param int $contentTypeCode content type code
     * @return Request self
     */
    public function setContentTypeCode($contentTypeCode) {
        $this->contentTypeCode = $contentTypeCode;
        return $this;
    }

    /**
     * Request content type getter
     * @return int content type code
     */
    public function getContentTypeCode() {
        return $this->contentTypeCode;
    }

    /**
     * Setter for request method
     * @param int $method method type code
     * @throws InvalidArgumentException
     */
    public function setMethod($method) {
        $method = (int) $method;
        switch ($this->method) {
            case self::METHOD_AUTO:
            case self::METHOD_GET:
            case self::METHOD_POST:
                $this->method = $method;
                break;
            default:
                throw new InvalidArgumentException();
        }
    }

    /**
     * Getter for request method
     * @return int method type code
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @return TransportInterface transport implementation
     */
    public function getTransport() {
        return $this->Transport;
    }

    /**
     * Transport object setter
     * @param int $transportCode transport implementation code
     * @throws InvalidArgumentException when was set incorrect transport code
     */
    public function setTransport($transportCode) {
        switch ($transportCode) {
            case self::TRANSPORT_CURL:
                $this->Transport = new CurlTransport();
                break;
            default:
                throw new InvalidArgumentException();
        }
    }

    /**
     * Add GET parameter
     * @param string $field parameter name
     * @param string $value parameter value
     * @return Request self
     */
    public function addGetField($field, $value) {
        $this->getData[$field] = (string) $value;
        return $this;
    }

    /**
     * Getter for GET parameters
     * @return array GET parameters
     */
    public function getGetData() {
        return $this->getData;
    }

    /**
     * Add POST parameter
     * @param string $field parameter name
     * @param string $value parameter value
     * @throw LogicException if post data is not array
     * @return Request self
     */
    public function addPostField($field, $value) {
        if (is_null($this->postData)) {
            $this->postData = array();
        }

        if (is_array($this->postData)) {
            $this->postData[$field] = (string) $value;
            return $this;
        } else {
            throw new LogicException('cannot set field for non array');
        }
    }

    /**
     * Setter for POST parameters
     * @param mixed $data parameters
     * @return Request self
     */
    public function setPostData($data) {
        $this->postData = $data;
        return $this;
    }

    /**
     * Getter for POST parameters
     * @return mixed POST parameters
     */
    public function getPostData() {
        return $this->postData;
    }

    /**
     * Add url parameter
     * @param string $field parameter name
     * @param string $value parameter value. Default empty
     * @return Request self
     */
    public function addUrlField($field, $value = '') {
        $this->urlData[$field] = (string) $value;
        return $this;
    }

    /**
     * Getter for url parameters
     * @return array url parameters
     */
    public function getUrlData() {
        return $this->urlData;
    }

    /**
     * URL string setter
     * @param string $url URL
     * @return Request self
     */
    public function setUrl($url) {
        $this->url = (string) $url;
        return $this;
    }

    /**
     * URL string getter
     * @return string URL string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Add header
     * @param string $name header name
     * @param string $value header value
     * @return Request self
     */
    public function addHeader($name, $value) {
        $this->headers[$name] = (string) $value;
        return $this;
    }

    /**
     * Setter for request headers
     * @param array $headers request headers array
     * @return Request self
     */
    public function setHeaders(array $headers) {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Getter for request headers
     * @return array request headers array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Setter for connect timeout
     * @param int $connectTimeout connect timeout value, seconds
     * @throws InvalidArgumentException for negative connect timeout
     * @return Request self
     */
    public function setConnectTimeout($connectTimeout) {
        if ($connectTimeout < 0) {
            throw new InvalidArgumentException();
        }
        $this->connectTimeout = (int) $connectTimeout;
        return $this;
    }

    /**
     * Getter for connect timeout
     * @return int connect timeout, seconds
     */
    public function getConnectTimeout() {
        return $this->connectTimeout;
    }

    /**
     * Setter for request timeout
     * @param int $timeout request timeout, seconds
     * @throws InvalidArgumentException for negative request timeout
     * @return Request self
     */
    public function setTimeout($timeout) {
        if ($timeout < 0) {
            throw new InvalidArgumentException();
        }
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Getter for request timeout
     * @return int request timeout, seconds
     */
    public function getTimeout() {
        return $this->timeout;
    }

    /**
     * @return int SSL version code
     */
    public function getSslVersion() {
        return $this->sslVersion;
    }

    /**
     * @param int $version SSL version code
     * @return $this self instance
     */
    public function setSslVersion($version) {
        $this->sslVersion = (int) $version;
        return $this;
    }

    /**
     * @return bool is request SSL version must be default or not
     */
    public function isDefaultSslVersion() {
        return $this->getSslVersion() === 0;
    }
}
