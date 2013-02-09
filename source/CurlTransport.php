<?php

namespace Network\Http;

use Network\TransportInterface;

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

        curl_setopt_array($Resource, array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_CONNECTTIMEOUT  => $this->Request->getConnectTimeout(),
            CURLOPT_TIMEOUT         => $this->Request->getTimeout(),
        ));

        $this->addHeaders($Resource, $this->Request->getHeaders());
        $this->addPostData($Resource, $this->Request->getPostData());

        $result = curl_exec($Resource);
        if ($result === false) {
            $errorCode = curl_errno($Resource);
            $errorMessage = curl_error($Resource);
            curl_close($Resource);
            throw new CurlErrorException($errorMessage, $errorCode);
        }

        $httpCode = curl_getinfo($Resource, CURLINFO_HTTP_CODE);
        curl_close($Resource);
        switch (floor($httpCode / 100)) {
            case 1:
                throw new HttpInformationalCodeException($result, $httpCode);
            case 3:
                throw new HttpRedirectionCodeException($result, $httpCode);
            case 4:
                throw new HttpClientErrorCodeException($result, $httpCode);
            case 5:
                throw new HttpServerErrorCodeException($result, $httpCode);
            default:
            case 2:
                return $result;
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
     * @param array $data POST parameters
     * @throws HttpContentTypeException when use unsupported type of content
     */
    private function addPostData($Resource, $data) {
        if (!empty($data)) {
            switch ($this->Request->getContentTypeCode()) {
                case Request::CONTENT_TYPE_UNDEFINED:
                    $string = http_build_query($data);
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
    private function addUrlData($url, $data) {
        if (strpos($url, '?') !== false) {
            throw new \InvalidArgumentException();
        }

        $parts[] = trim($url, '/');
        foreach ($data as $key => $value) {
            if ($value) {
                $parts[] = urlencode($key .'/' . $value);
            } else {
                $parts[] = urlencode($key);
            }
        }
        return implode('/', $parts);
    }
}

/**
 * libcurl error exception
 */
final class CurlErrorException extends TransportException {}