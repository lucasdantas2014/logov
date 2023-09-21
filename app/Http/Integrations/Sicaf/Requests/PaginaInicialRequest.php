<?php

namespace App\Http\Integrations\Sicaf\Requests;

use GuzzleHttp\RequestOptions;
use Saloon\Http\SoloRequest;
use Saloon\Enums\Method;

class PaginaInicialRequest extends SoloRequest
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/private/index.jsf';
    }

    protected function defaultConfig(): array
    {
        return [
            RequestOptions::VERIFY => false,
//            'proxy' => 'http://localhost.charlesproxy.com:8888',
            'allow_redirects' => false
        ];
    }
}
