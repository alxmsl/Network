<?php
/*
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */

namespace alxmsl\Network\Http;
use alxmsl\Network\Exception\CurlErrorException;
use alxmsl\Network\Exception\HttpClientErrorCodeException;
use alxmsl\Network\Exception\HttpContentTypeException;
use alxmsl\Network\Exception\HttpInformationalCodeException;
use alxmsl\Network\Exception\HttpRedirectionCodeException;
use alxmsl\Network\Exception\HttpServerErrorCodeException;
use alxmsl\Network\TransportInterface;
use DOMDocument;

/**
 * Class for support transport via libcurl
 * @author alxmsl
 * @date 1/13/13
 */ 
final class CurlTransport implements TransportInterface {
    /**
     * @var Request request for transportation
     */
    private $Request = null;

    /**
     * @var array response headers data
     */
    private $responseHeaders = array();

    /**
     * @var array additional curl options
     */
    private $options = array();

    /**
     * Setter for request object
     * @param Request $Request request object
     * @return CurlTransport self
     */
    public function setRequest($Request) {
        $this->Request = $Request;
        return $this;
    }

    /**
     * Getter for request object
     * @return Request|null request object or null, if it's not present
     */
    public function getRequest() {
        return $this->Request;
    }

    /**
     * Getter for response headers data
     * @return array response headers data
     */
    public function getResponseHeaders() {
        return $this->responseHeaders;
    }

    /**
     * Transportation implementation method
     * @return string request execution result
     * @throws HttpRedirectionCodeException if http redirect code accepted
     * @throws HttpClientErrorCodeException if http client error code accepted
     * @throws HttpInformationalCodeException if http information code accepted
     * @throws HttpServerErrorCodeException if http server error code accepted
     * @throws CurlErrorException if libcurl error generated
     */
    public function makeHttpRequest() {
        $url = $this->addUrlData($this->Request->getUrl(), $this->Request->getUrlData());
        $url = $this->addGetData($url, $this->Request->getGetData());
        $Resource = curl_init($url);

        $options = [
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_CONNECTTIMEOUT  => $this->Request->getConnectTimeout(),
            CURLOPT_TIMEOUT         => $this->Request->getTimeout(),
            CURLOPT_HEADER          => true,
        ];
        if (!$this->Request->isDefaultSslVersion()) {
            $options[CURLOPT_SSLVERSION] = $this->Request->getSslVersion();
        }
        curl_setopt_array($Resource, $this->getOptions() + $options);

        $this->addHeaders($Resource, $this->Request->getHeaders());
        $this->addPostData($Resource, $this->Request->getPostData());
        $this->setMethod($Resource, $this->Request->getMethod());

        $result = curl_exec($Resource);
        if ($result === false) {
            $errorCode = curl_errno($Resource);
            $errorMessage = curl_error($Resource);
            curl_close($Resource);
            throw new CurlErrorException($errorMessage, $errorCode);
        }

        $headerSize = curl_getinfo($Resource, CURLINFO_HEADER_SIZE);
        $headers = substr($result, 0, $headerSize);
        $this->parseResponseHeaders($headers);

        $body = substr($result, $headerSize);
        $httpCode = curl_getinfo($Resource, CURLINFO_HTTP_CODE);
        curl_close($Resource);
        switch (floor($httpCode / 100)) {
            case 1:
                throw new HttpInformationalCodeException($body, $httpCode);
            case 3:
                throw new HttpRedirectionCodeException($body, $httpCode);
            case 4:
                throw new HttpClientErrorCodeException($body, $httpCode);
            case 5:
                throw new HttpServerErrorCodeException($body, $httpCode);
            default:
            case 2:
                return $body;
        }
    }

    /**
     * Get all manually setted curl options
     * @return array assotiative array of curl options
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * Add curl option
     * @param int $option one of CURLOPT_* constant
     * @param mixed $value curl option value
     */
    public function setOption($option, $value) {
        $this->options[$option] = $value;
    }

    /**
     * Parsing response block for headers
     * @param string $string response block
     */
    private function parseResponseHeaders($string) {
        $parts = explode("\n", $string);
        foreach ($parts as $part) {
            if (strpos($part, ':') !== false) {
                list($name, $value) = explode(':', $part);
                $this->responseHeaders[trim($name)] = trim($value);
            }
        }
    }

    /**
     * Add http headers method
     * @param resource $Resource libcurl handler
     * @param array $headers assotiative array of http headers and values
     */
    private function addHeaders($Resource, $headers) {
        $httpHeaders = array();
        foreach ($headers as $header => $value) {
            $httpHeaders[] = $header . ': ' . $value;
        }
        curl_setopt($Resource, CURLOPT_HTTPHEADER, $httpHeaders);
    }

    /**
     * Add GET data for resource
     * @param string $url resource url
     * @param array $data GET parameters
     * @return string query string
     */
    private function addGetData($url, $data) {
        $parts[] = $url;
        if (!empty($data)) {
            $parts[] = http_build_query($data);
        }
        return implode('?', $parts);
    }

    /**
     * Add POST data for request
     * @param resource $Resource libcurl handler
     * @param mixed $data POST parameters
     * @throws HttpContentTypeException when use unsupported type of content
     */
    private function addPostData($Resource, $data) {
        if (!empty($data)) {
            switch ($this->Request->getContentTypeCode()) {
                case Request::CONTENT_TYPE_UNDEFINED:
                    $string = http_build_query($data);
                    break;
                case Request::CONTENT_TYPE_JSON:
                    $string = json_encode($data);
                    break;
                case Request::CONTENT_TYPE_TEXT:
                    $string = (string) $data;
                    break;
                case Request::CONTENT_TYPE_XML:
                    if ($data instanceof DOMDocument) {
                        $string = $data->saveXML();
                    } else {
                        $string = (string) $data;
                    }
                    break;
                default:
                    throw new HttpContentTypeException();
            }
            curl_setopt_array($Resource, array(
                CURLOPT_POST    => true,
                CURLOPT_POSTFIELDS => $string,
            ));
        }
    }

    /**
     * Add url parameters for url
     * @param string $url base url
     * @param array $data url parameters key-value data
     * @throws \InvalidArgumentException when url already contains GET data
     * @return string query string
     */
    private function addUrlData($url, array $data) {
        // Do nothing, if there is no url data
        if (empty($data)) {
            return $url;
        }

        if (strpos($url, '?') !== false) {
            throw new \InvalidArgumentException();
        }

        $parts[] = trim($url, '/');
        foreach ($data as $key => $value) {
            if ($value) {
                $parts[] = urlencode($key) .'/' . urlencode($value);
            } else {
                $parts[] = urlencode($key);
            }
        }
        return implode('/', $parts);
    }

    /**
     * Set request HTTP method
     * @param resource $Resource libcurl handler
     * @param int $method HTTP method identifier
     */
    private function setMethod($Resource, $method) {
        switch ($method) {
            case Request::METHOD_GET:
                curl_setopt($Resource, CURLOPT_HTTPGET, true);
                break;
            case Request::METHOD_POST:
                curl_setopt($Resource, CURLOPT_POST, true);
                break;
        }
    }
}
