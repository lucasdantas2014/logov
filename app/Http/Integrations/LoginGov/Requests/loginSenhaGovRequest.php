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

class loginSenhaGovRequest extends Request implements HasBody
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
    protected $password;

    public function __construct($idAutorizacao, $csrfId, $cpf, $password, $hcaptcha)
    {
        $this->idAutorizacao = $idAutorizacao;
        $this->csrfId = $csrfId;
        $this->cpf = $cpf;
        $this->password = $password;
        $this->hcaptcha = $hcaptcha;
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

    protected function defaultHeaders(): array
    {
        return [
//            user-agent	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36
            ];
    }

    protected function defaultBody(): array
    {
        return [
            '_csrf' => $this->csrfId,
            'accountId' => $this->cpf,
            'password' => $this->password,
            'h-captcha-response' => $this->hcaptcha,
            'operation' => 'enter-password'
        ];
    }

    protected function defaultConfig(): array
    {
        return [
//            RequestOptions::VERIFY => false,
//            'proxy' => 'http://localhost.charlesproxy.com:8888',
            'allow_redirects' => false

        ];
    }
}
