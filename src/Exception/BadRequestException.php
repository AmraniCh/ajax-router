<?php

namespace AmraniCh\AjaxRouter\Exception;

/**
 * AmraniCh\AjaxRouter\Exception\BadRequestException
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 * @internal
 */
class BadRequestException extends AjaxRouterException
{
    public function __construct($message, $code = 400)
    {
        parent::__construct($message, $code);
    }
}
