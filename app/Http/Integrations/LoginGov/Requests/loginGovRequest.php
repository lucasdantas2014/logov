<?php

namespace App\Http\Integrations\LoginGov\Requests;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\SoloRequest;
use Saloon\Traits\Body\HasFormBody;
use Saloon\Traits\Body\HasJsonBody;

class loginGovRequest extends Request implements HasBody
{

    use HasFormBody;
    /**
     * Define the HTTP method
     *
     * @var Method
     */
    protected Method $method = Method::POST;

    protected $idAutorizacao;
    protected $csrfId;
    protected $cpf;

    public function __construct($idAutorizacao, $csrfId, $cpf)
    {
        $this->idAutorizacao = $idAutorizacao;
        $this->csrfId = $csrfId;
        $this->cpf = $cpf;
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

    protected function defaultQuery(): array
    {
        return [
            'client_id' => 'sicaf.gov.br',
            'authorization_id' => $this->idAutorizacao
        ];
    }

    protected function defaultBody(): array
    {
        return [
            '_csrf' => $this->csrfId,
            'accountId' => $this->cpf,
            'operation' => 'enter-account-id'
        ];
    }
}
