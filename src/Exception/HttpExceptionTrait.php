<?php

namespace AmraniCh\AjaxRouter\Exception;

/**
 * AmraniCh\AjaxRouter\Exception\HttpExceptionTrait
 * 
 * This trait supports sending HTTP status codes for the next response, the status code 
 * specified when creating a new class of the exception in the second parameter, if no 
 * value given the status code 500 will be sent by default.
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 */
trait HttpExceptionTrait
{
    public function __construct($message, $code, $headers = [])
    {
        // check if the headers was already sent
        if (!empty($headers) && !headers_sent()) {
            http_response_code($code);

            foreach($headers as $name => $value) {
                header("$name: $value");
            }
        }
    }
}   

