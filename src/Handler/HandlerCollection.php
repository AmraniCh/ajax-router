<?php

namespace AmraniCh\AjaxDispatcher\Handler;

use AmraniCh\AjaxDispatcher\Exception\InvalidArgumentException;

/**
 * AmraniCh\AjaxDispatcher\Handler\HandlerCollection
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 */
class HandlerCollection
{
    /** @var Handler[] */
    protected $handlers;

    /**
     * @param Handler[] $handlers
     */
    public function __construct(array $handlers)
    {
        $this->setHandlers($handlers);
    }

    /**
     * @return Handler[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @param array $handlers
     * 
     * @return HandlerCollection
     */
    public function setHandlers(array $handlers)
    {
        foreach ($handlers as $handler) {
            if (is_object($handler) && !$handler instanceof Handler) {
                throw new InvalidArgumentException(sprintf(
                    "An AJAX request handler must be an instance of '%s' class, given '%s'.",
                    Handler::class,
                    get_class($handler)
                ));
            }
        }

        $this->handlers = $handlers;

        return $this;
    }

    /**
     * @param Handler $handler
     * 
     * @return HandlerCollection
     */
    public function addHandler(Handler $handler)
    {
        $this->handlers[] = $handler;

        return $this;
    }
}
