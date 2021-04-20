<?php

namespace Idunis\EventSauce\Support\Concerns;

use GuzzleHttp\Client;

trait ClientConsumer
{
    protected $baseUri;
    protected $secret;

    private function performRequest($method, $requestUrl, $formParams, $headers)
    {
        $client = new Client([
            "base_uri" => $this->baseUri
        ]);
        
        if(isset($this->secret)) {
            $headers["Authorization"] = $this->secret;
        }
        
        $response = $client->request($method, $requestUrl, [
            "form_params" => $formParams,
            "headers" => $headers
        ]);
        
        return $response->getBody()->getContents();
    }

    public function get($requestUrl, $formParams = [], $headers = [])
    {
        return $this->performRequest("GET", $requestUrl, $formParams, $headers);
    }

    public function post($requestUrl, $formParams = [], $headers = [])
    {
        return $this->performRequest("POST", $requestUrl, $formParams, $headers);
    }

    public function put($requestUrl, $formParams = [], $headers = [])
    {
        return $this->performRequest("PUT", $requestUrl, $formParams, $headers);
    }

    public function delete($requestUrl, $formParams = [], $headers = [])
    {
        return $this->performRequest("DELETE", $requestUrl, $formParams, $headers);
    }
}