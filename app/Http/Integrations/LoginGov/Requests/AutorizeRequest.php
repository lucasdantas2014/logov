<?php

namespace App\Http\Integrations\LoginGov\Requests;

use GuzzleHttp\RequestOptions;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\SoloRequest;

class AutorizeRequest extends Request
{
    /**
     * Define the HTTP method
     *
     * @var Method
     */
    protected Method $method = Method::GET;


    public function __construct(
        protected string $sessionId,
    ) { }


    /**
     * Define the endpoint for the request
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return '/authorize';
    }

    protected function defaultQuery(): array
    {
        return [
            'response_type' => 'code',
            'client_id' => 'sicaf.gov.br',
            'scope' => 'openid profile email phone govbr_confiabilidades',
            'redirect_uri' => 'https://www3.comprasnet.gov.br/sicaf-web/public/pages/security/retornoLoginSsoFornecedor.jsf',
            'nonce' =>  $this->sessionId,
            'state' => $this->sessionId
        ];
    }
}
