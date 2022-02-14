<?php

namespace AmraniCh\AjaxRouter\Exception;

/**
 * AmraniCh\AjaxRouter\Exception\AjaxRouterException
 * 
 * Generic exception class for the library.
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 */
class AjaxRouterException extends \Exception
{
    use HttpExceptionTrait {
        HttpExceptionTrait::__construct as private __HttpExceptionTraitConstructor;
    }

    public function __construct($message, $code = 500, $headers = [])
    {
        parent::__construct($message, $code);
        $this->__HttpExceptionTraitConstructor($message, $code, $headers);
    }
}   
