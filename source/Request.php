<?php

namespace Network\Http;

use Network\RequestInterface,
    Network\TransportInterface;

/**
 * Class for Http request
 * @author alxmsl
 * @date 1/13/13
 */ 
final class Request implements RequestInterface {
    /**
     * Content type constants
     */
    const   CONTENT_TYPE_UNDEFINED = 0;

    /**
     * Transport library constants
     */
    const TRANSPORT_CURL    = 0;

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
    private $headers = array();

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
     * @var array GET parameters
     */
    private $getData = array();

    /**
     * @var array POST parameters
     */
    private $postData = array();

    /**
     * @var array url parameters
     */
    private $urlData = array();

    /**
     * Send request data method
     * @return string request execution result
     */
    public function send() {
        if (is_null($this->Transport)) {
            throw new TransportException();
        }
        $this->Transport->setRequest($this);
        return $this->Transport->makeHttpRequest($this);
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
     * Transport object setter
     * @param int $transportCode transport implemetation code
     * @throws \InvalidArgumentException when was set incorrect transport code
     */
    public function setTransport($transportCode) {
        switch ($transportCode) {
            case self::TRANSPORT_CURL:
                $this->Transport = new CurlTransport();
                break;
            default:
                throw new \InvalidArgumentException();
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
     * @return Request self
     */
    public function addPostField($field, $value) {
        $this->postData[$field] = (string) $value;
        return $this;
    }

    /**
     * Getter for POST parameters
     * @return array POST parameters
     */
    public function getPostData() {
        return $this->postData;
    }

    /**
     * Add url parameter
     * @param string $field parameter name
     * @param string $value parameter value
     * @return Request self
     */
    public function addUrlField($field, $value) {
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
     * @throws \InvalidArgumentException for negative connect timeout
     * @return Request self
     */
    public function setConnectTimeout($connectTimeout) {
        if ($connectTimeout < 0) {
            throw new \InvalidArgumentException();
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
     * @throws \InvalidArgumentException for negative request timeout
     * @return Request self
     */
    public function setTimeout($timeout) {
        if ($timeout < 0) {
            throw new \InvalidArgumentException();
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
}

/**
 * Base http request exception
 */
class HttpException extends \Exception {}

/**
 * Base exception for not success http codes
 */
class HttpCodeException extends HttpException {}

/**
 * Exception for http 1xx informational codes
 */
final class HttpInformationalCodeException extends HttpCodeException {}

/**
 * Exception for http 3xx redirection codes
 */
final class HttpRedirectionCodeException extends HttpCodeException {}

/**
 * Exception for http 4xx client errors codes
 */
final class HttpClientErrorCodeException extends HttpCodeException {}

/**
 * Exception for http 5xx server error codes
 */
final class HttpServerErrorCodeException extends HttpCodeException {}

/**
 * Exception for illegal request content types
 */
final class HttpContentTypeException extends HttpException {}

/**
 * Base class for transportation errors
 */
class TransportException extends \Exception {}