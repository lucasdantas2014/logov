<?php

namespace App\Http\Integrations\LoginGov\Requests;

use GuzzleHttp\RequestOptions;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\SoloRequest;

class govInicialRedirectRequest extends Request
{
    /**
     * Define the HTTP method
     *
     * @var Method
     */
    protected Method $method = Method::GET;

    protected $idAutorizacao;
    public function __construct($idAutorizacao)
    {
        $this->idAutorizacao = $idAutorizacao;
    }

    /**
     * Define the endpoint for the request
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return '/login';
    }

    public function defaultQuery(): array
    {
        return [
            'client_id' => 'sicaf.gov.br',
            'authorization_id' => $this->idAutorizacao
        ];
    }
}
