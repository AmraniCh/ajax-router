<?php

namespace AmraniCh\AjaxDispatcher\Http;

/**
 * AmraniCh\AjaxDispatcher\Http\Response
 *
 * Simple HTTP response class implementation.
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 */
class Response
{
    /** @var int */
    protected $statusCode;

    /** @var mixed */
    protected $body;

    /** @var array */
    protected $headers;

    public function __construct($body = null, $statusCode = 200, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->body       = $body;
        $this->headers    = $headers;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param int $statusCode
     * 
     * @return Response
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param mixed $body
     * 
     * @return Response
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param array $headers
     * 
     * @return Response
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        
        return $this;
    }

    /**
     * Add new header to the response headers.
     * 
     * @param string $name
     * @param string $value
     *
     * @return Response
     */
    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Sends the content of the response.
     * 
     * @return Response
     */
    public function send()
    {
        return $this->sendContent($this->body);
    }

    /**
     * Sends the response content in json format.
     *
     * @return Response
     */
    public function sendJson()
    {
        $this->setBody(json_encode($this->body));

        $this->addHeader('Content-type', 'application/json');

        return $this->sendContent();
    }

    /**
     * Creates a json reponse object.
     *
     * @param mixed $body
     * @param int   $statusCode
     * @param array $headers
     *
     * @return Response
     */
    public static function json($body, $statusCode = 200, $headers = [])
    {
        $response = new static($body, $statusCode, $headers);

        $response->addHeader('Content-type', 'application/json');

        return $response->setBody(json_encode($body));
    }

    /**
     * Creates a reponse object with raw data in the body.
     *
     * @param mixed $body
     * @param int   $statusCode
     * @param array $headers
     *
     * @return Response
     */
    public static function raw($body, $statusCode = 200, $headers = [])
    {
        $response = new static($body, $statusCode, $headers);

        $response->addHeader('Content-type', 'text/plain');

        return $response->setBody($body);
    }

    /**
     * Dumps the actual reponse content to the output buffer.
     *
     * @return Response
     */
    protected function sendContent()
    {
        $this->setResponseCode();
        $this->sendRawHeaders();

        echo ($this->body);

        return $this;
    }

    /**
     * Sets the Http status code.
     * 
     * @return void
     */
    protected function setResponseCode()
    {
        http_response_code($this->statusCode);
    }

    /**
     * Sends all response headers.
     *
     * @return void|null
     */
    protected function sendRawHeaders()
    {
        if (headers_sent() && empty($this->headers)) {
            return null;
        }

        foreach ($this->headers as $name => $value) {
            header(sprintf("%s: %s", ucfirst(strtolower($name)), $value), false);
        }
    }
}
