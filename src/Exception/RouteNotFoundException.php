<?php

namespace AmraniCh\AjaxRouter\Exception;

/**
 * AmraniCh\AjaxRouter\Exception\RouteNotFoundException
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 * @internal
 */
class RouteNotFoundException extends AjaxRouterException
{
    public function __construct($message, $code = 400)
    {
        parent::__construct($message, $code);
    }
}
