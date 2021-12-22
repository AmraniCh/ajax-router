<?php

namespace AmraniCh\AjaxDispatcher\Exception;

/**
 * AmraniCh\AjaxDispatcher\Exception\AjaxDispatcherException
 * 
 * Generic exception class for the library.
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 */
class AjaxDispatcherException extends \Exception
{
    use HttpExceptionTrait {
        HttpExceptionTrait::__construct as private __HttpExceptionTraitConstructor;
    }

    public function __construct($message, $code = 500, $headers = [])
    {
        $this->__HttpExceptionTraitConstructor($message, $code, $headers);
    }
}   
