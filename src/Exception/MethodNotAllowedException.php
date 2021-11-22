<?php

namespace AmraniCh\AjaxDispatcher\Exception;

/**
 * AmraniCh\AjaxDispatcher\Exception\MethodNotAllowedException
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 * @internal
 */
class MethodNotAllowedException extends AjaxDispatcherException
{
    public function __construct($message, $code = 405, $allowedMethods = [])
    {
        parent::__construct($message, $code, [
            "Allow" => $allowedMethods
        ]);
    }
}
