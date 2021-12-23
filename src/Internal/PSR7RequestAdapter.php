<?php

namespace AmraniCh\AjaxDispatcher\Internal;

use Psr\Http\Message\ServerRequestInterface;

/**
 * AmraniCh\AjaxDispatcher\Internal\PSR7RequestAdapter
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 * @internal
 */
class PSR7RequestAdapter
{
    /** @var ServerRequestInterface */
    protected $request;

    /**
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }
    
    /**
     * Gets request variables.
     *
     * @return array
     */
    public function getVariables()
    {
        if ($this->request->getMethod() === 'GET') {
            return $this->getUriQueryVariables();
        }

        return $this->getBodyVariables();
    }

    /**
     * @return array
     */
    protected function getUriQueryVariables()
    {
        if ($this->isJsonContentType()) {
            // because of the content passed in the URL we need to decode it
            $query = urldecode($this->request->getUri()->getQuery());
            return json_decode($query, true);
        }

        return $this->request->getQueryParams();
    }

    /**
     * @return array
     */
    protected function getBodyVariables()
    {
        if (($body = $this->request->getBody()->getContents()) === '') {
            return [];
        }

        if ($this->isJsonContentType()) {
            return json_decode($body, true);
        }

        parse_str($body, $variables);

        return $variables;
    }

    /**
     * Checks if the request content type is JSON type.
     *
     * @return bool
     */
    protected function isJsonContentType()
    {
        if (!$this->request->hasHeader('Content-Type')) {
            return false;
        }
        
        return strpos($this->request->getHeaderLine('Content-Type'), 'application/json') !== false;
    }
}
