<?php

namespace App\Http\Integrations\Sicaf\Requests;

use GuzzleHttp\RequestOptions;
use Saloon\Http\Request;
use Saloon\Http\SoloRequest;
use Saloon\Enums\Method;

class PaginaRetornoLoginRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/public/pages/security/retornoLoginSsoFornecedor.jsf';
    }

    protected function defaultConfig(): array
    {
        return [
            RequestOptions::VERIFY => false,
            'proxy' => 'http://localhost.charlesproxy.com:8888',
            'allow_redirects' => false
        ];
    }
}
