<?php

namespace AmraniCh\AjaxRouter\Psr7;

use Psr\Http\Message\ResponseInterface;

/**
 * Simple PSR7 response compatible sender.
 *
 * AmraniCh\AjaxRouter\Psr7\PSR7ResponseSender
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 */
class PSR7ResponseSender
{
    /** @var ResponseInterface */
    protected $response;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Send the response content.
     *
     * @return void
     */
    public function send()
    {
        $this->sendResponseCode();
        $this->sendHeaders();

        echo $this->response->getBody();
    }

    /**
     * Set the HTTP status code for the next response.
     *
     * @return bool|int
     */
    protected function sendResponseCode()
    {
        return http_response_code($this->response->getStatusCode());
    }

    /**
     * Send the response headers.
     *
     * @return void|null
     */
    protected function sendHeaders()
    {
        $headers = $this->response->getHeaders();

        if (headers_sent() && empty($headers)) {
            return null;
        }

        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                header(
                    sprintf("%s: %s", ucfirst(strtolower($name)), $value),
                    false
                );
            }
        }
    }
}
