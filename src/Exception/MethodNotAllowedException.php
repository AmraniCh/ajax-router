<?php

namespace AmraniCh\AjaxRouter\Exception;

/**
 * AmraniCh\AjaxRouter\Exception\MethodNotAllowedException
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 * @internal
 */
class MethodNotAllowedException extends AjaxRouterException
{

    public function __construct($message, $code = 405, $allowedMethods = [])
    {
        parent::__construct($message, $code, [
            "Allow" => $this->formatAllowHeaderValue($allowedMethods)
        ]);
    }

    /**
     * @param array $methods
     * @return string
     */
    protected function formatAllowHeaderValue(array $methods)
    {
        if (empty($methods)) {
            return '';
        }

        return implode(', ', $methods);
    }
}
