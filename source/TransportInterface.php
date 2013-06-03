<?php

namespace Network;

/**
 * Interface for request transportation objects
 * @author alxmsl
 * @date 1/13/13
 */
interface TransportInterface {
    /**
     * Setter for request object
     * @param RequestInterface $Request request object
     * @return TransportInterface self
     */
    public function setRequest($Request);

    /**
     * Getter for request object
     * @return RequestInterface|null request object or null, if it's not present
     */
    public function getRequest();

    /**
     * Getter for response headers data
     * @return array response headers data
     */
    public function getResponseHeaders();

    /**
     * Transportation implementation method
     * @return string request execution result
     */
    public function makeHttpRequest();
}