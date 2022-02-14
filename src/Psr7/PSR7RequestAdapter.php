<?php

namespace AmraniCh\AjaxRouter\Psr7;

use Psr\Http\Message\ServerRequestInterface;

/**
 * AmraniCh\AjaxRouter\Psr7\PSR7RequestAdapter
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
        if ($this->isJsonContentType()) {
            return json_decode($this->request->getBody()->getContents(), true); 
        }

        if ($this->isUrlencodedContentType()) {  
            if (($body = $this->request->getParsedBody()) === '') {
                return [];
            }
            
            return $body;
        }

        if ($this->isFormDataContentType()) {
            return array_merge(
                $this->request->getParsedBody(),
                $this->request->getUploadedFiles()
            );
        }

        // TODO should throw an exception if response content type is not present
        // or it value is invalid ?

        return [];
    }

    /**
     * Checks if the request content type is JSON type.
     *
     * @return bool
     */
    protected function isJsonContentType()
    {
        return $this->isHeaderContains(
            'Content-Type', 
            'application/json'
        );
    }

    /**
     * @return bool
     */
    protected function isUrlencodedContentType()
    {
        return $this->isHeaderContains(
            'Content-Type', 
            'application/x-www-form-urlencoded'
        );
    }

    /**
     * @return bool
     */
    protected function isFormDataContentType()
    {
        return $this->isHeaderContains(
            'Content-Type', 
            'multipart/form-data'
        );
    }

    /**
     * @return bool
     */
    protected function isHeaderContains($header, $value)
    {
        if (!$this->request->hasHeader($header)) {
            return false;
        }
        
        return strpos($this->request->getHeaderLine($header), $value) !== false;
    }
}
