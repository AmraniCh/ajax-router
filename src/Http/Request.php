<?php

namespace AmraniCh\AjaxDispatcher\Http;

use AmraniCh\AjaxDispatcher\Exception\LogicException;
use AmraniCh\AjaxDispatcher\Exception\BadRequestException;
use AmraniCh\AjaxDispatcher\Exception\InvalidArgumentException;

/**
 * AmraniCh\AjaxDispatcher\Http\Request
 *
 * Simple class that represents an HTTP request.
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 */
class Request
{
    /** @var array */
    protected $server;

    /** @var array */
    const HTTP_METHODS = [
        'GET',
        'HEAD',
        'POST',
        'PUT',
        'DELETE',
        'CONNECT',
        'TRACE',
        'PATCH'
    ];

    /** @var string */
    protected $method;

    /** @var array */
    protected $headers = [];

    /** @var array */
    protected $variables = [];

    /** @var string */
    protected $query;

    /** @var string */
    protected $body;

    /**
     * @param array $server
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $server)
    {
        $this->setServer($server);
        $this->setMethod($this->server['REQUEST_METHOD']);
        $this->setHeaders($this->getAllHeaders());
        $this->setBody($this->getRequestBody() ?: '');
        $this->setQuery($this->getQueryString());
        $this->setVariables($this->extractVariables());
    }

    /**
     * Allows calling request variables as a properties of the request object.
     *
     * @throws LogicException
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->variables)) {
            throw new LogicException("The parameter '$name' not exists in the request variables.");
        }

        return $this->variables[$name];
    }

    /**
     * @return array
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param array $server
     *
     * @return Request
     */
    public function setServer(array $server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @param string $method
     *
     * @return Request
     * @throws InvalidArgumentException
     */
    public function setMethod($method)
    {
        if (!in_array($method, static::HTTP_METHODS)) {
            throw new InvalidArgumentException("HTTP request method '$method' not supported.");
        }

        $this->server['REQUEST_METHOD'] = $method;

        $this->method = $method;

        return $this;
    }

    /**
     * @param array $headers
     *
     * @return Request
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param array $variables
     *
     * @return Request
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Throws an exception if the request is not an AJAX request.
     *
     * @return Request
     * @throws BadRequestException
     */
    public function throwIfNotAJAXRequest()
    {
        if (!$this->isAjaxRequest()) {
            throw new BadRequestException('AjaxDispatcher Accept only AJAX requests.');
        }

        return $this;
    }

    /**
     * Checks if the HTTP request is issued using the XMLHttpRequest in the client side.
     *
     * To recognize AJAX requests from the other regular HTTP requests the method will look for a header that must be
     * present with the current request headers which is the 'X-Requested-With' header, and the value of it must be
     * 'XMLHttpRequest', if you use vanilla JavaScript this header will be not submitted automatically by the browser,
     * so you have to manually add it to the request headers, but if you use some library like jQuery or Axios there
     * is no need for that since the lib will handle this for you.
     *
     * @return bool
     */
    public function isAjaxRequest()
    {
        return array_key_exists('HTTP_X_REQUESTED_WITH', $this->headers)
            && strtolower($this->getHeaderValue('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }

    public function getHeaderValue($name)
    {
        if (!array_key_exists($name, $this->getHeaders())) {
            return null;
        }

        return $this->headers[$name];
    }

    /**
     * Gets URL query string.
     *
     * @return string|null
     */
    public function getQueryString()
    {
        if (!array_key_exists('QUERY_STRING', $this->server)) {
            return null;
        }

        return $this->server['QUERY_STRING'];
    }

    /**
     * Extracts all variables from the request and return them as an associative array.
     *
     * @return array
     */
    protected function extractVariables()
    {
        $contentType = $this->getHeaderValue('HTTP_CONTENT_TYPE');
        $isJson      = !is_null($contentType) && strpos($contentType, 'application/json') !== false;

        if ($this->method === 'GET') {
            if ($isJson) {
                // because of the content passed in the URL we need to decode it
                $query = urldecode($this->getQuery());
                return json_decode($query, true);
            }

            parse_str($this->getQuery(), $variables);
            return $variables;
        }

        if (($body = $this->getBody()) === '') {
            return [];
        }

        if ($isJson) {
            return json_decode($body, true);
        }

        parse_str($body, $variables);

        return $variables;
    }

    /**
     * Gets the request body data.
     *
     * @return string|null
     */
    protected function getRequestBody()
    {
        if (($body = file_get_contents("php://input")) === false) {
            return null;
        }

        return $body;
    }

    /**
     * Gets all the HTTP request headers.
     *
     * @return array
     */
    protected function getAllHeaders()
    {
        $headers = [];

        foreach ($this->server as $name => $value) {
            if (substr($name, 0, 4) !== 'HTTP') {
                continue;
            }
            $headers[$name] = $value;
        }

        return $headers;
    }
}
