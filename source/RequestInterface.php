<?php

namespace Network;

/**
 * Request interface
 * @author alxmsl
 * @date 1/13/13
 */ 
interface RequestInterface {
    /**
     * Send request data method
     * @return string request execution result
     */
    public function send();
}
