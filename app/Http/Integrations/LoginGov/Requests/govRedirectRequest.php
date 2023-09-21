<?php

namespace App\Http\Integrations\LoginGov\Requests;

use GuzzleHttp\RequestOptions;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\SoloRequest;

class govRedirectRequest extends Request
{
    /**
     * Define the HTTP method
     *
     * @var Method
     */
    protected Method $method = Method::GET;

    /**
     * Define the endpoint for the request
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return 'http://sso.acesso.gov.br/';
    }
}
