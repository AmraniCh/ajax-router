<?php

namespace AmraniCh\AjaxDispatcher\Exception;

/**
 * AmraniCh\AjaxDispatcher\Exception\HandlerNotFoundException
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 * @internal
 */
class HandlerNotFoundException extends AjaxDispatcherException
{
    public function __construct($message, $code = 400)
    {
        parent::__construct($message, $code);
    }
}
