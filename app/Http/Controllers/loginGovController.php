<?php

namespace App\Http\Controllers;

use App\Http\Integrations\LoginGov\LoginGovConnector;
use App\Http\Integrations\LoginGov\Requests\AutorizeRequest as AutorizeGovRequest;
use App\Http\Integrations\LoginGov\Requests\govInicialRedirectRequest;
use App\Http\Integrations\LoginGov\Requests\govRedirectRequest;
use App\Http\Integrations\LoginGov\Requests\loginGovRequest;
use App\Http\Integrations\LoginGov\Requests\loginSenhaGovRequest;
use App\Http\Integrations\Sicaf\Requests\PaginaInicialRequest;
use App\Http\Integrations\Sicaf\Requests\PaginaLoginRequest;
use App\Http\Integrations\Sicaf\Requests\PaginaRetornoLoginRequest;
use App\Http\Integrations\Sicaf\SicafConnector;
use Illuminate\Http\Request;
use Saloon\Contracts\Connector;
use Saloon\Contracts\Response;

class loginGovController extends Controller
{

    private function getCookiesString(Response $response) {

        $cookies = $response->header('set-cookie') ?? $response->header('Set-Cookie');

        dump($response->header('set-cookie'));
        dump($response->header('Set-Cookie'));
        if (empty($cookies)) {
            return '';
        }

        if (is_array($cookies)) {
            $cookiesString = '';
            $cont = 0;
            foreach ($cookies as $cookie) {
                if ($cont > 0) {
                    $cookiesString .= ';';
                }
                $cookiesString .= explode(';', $cookie)[0];
                $cont++;
            }
            return $cookiesString;

        }
        return explode(';', $cookies)[0];
    }
    public function login_sicaf(Request $request) {

        $sicafConnector = new SicafConnector();
        dump('pegando valores');
        $login = $request->input('login');
        $senha = $request->input('senha');

        dump('acessando pagina sicaf');
        list($sessionId, $cookies) = $this->pegarSessionIdSicaf($sicafConnector);

        dump('setando cookies iniciais');
        $sicafConnector->headers()->add('Cookie', $cookies);

        dump('realizando login...');
        $queryParams = $this->realizarLoginComGov($sessionId, $login, $senha);

        if (empty($queryParams)) {
            $queryParams = $this->realizarLoginComGov($sessionId, $login, $senha);
        }

        $queryParams['state'] = $sessionId;

        $this->acessarPagina($sicafConnector, $queryParams);

        return response()->json(
            [
                'sessionId' => $sessionId,
                'mensagem' => 'login efetuado com sucesso'
            ],
            200);
    }

    private function acessarPagina(Connector $sicafConnector, $queryParams) {

        $paginaRetorno = new PaginaRetornoLoginRequest();
        $paginaRetorno->query()->set($queryParams);
        $reponsePaginaRetorno = $sicafConnector->send($paginaRetorno);

        $paginaInicial = new PaginaInicialRequest();
        $sicafConnector->send($paginaInicial);
    }

    private function pegarSessionIdSicaf(Connector $sicafConnector)
    {
        $paginaInicial = new PaginaLoginRequest();

        $response = $sicafConnector->send($paginaInicial);

        preg_match('/(\/sicaf-web\/index.jsf;jsession.*)\" e/',
            $response->body(),
            $resultadoRegexButton);

        $url = $resultadoRegexButton[1];

        preg_match('/jsessionid=(.*)/', $url, $resultadoJsessionId);
        $sessionId = str_replace(['.', '_'], '', $resultadoJsessionId[1]);

        $cookies = $this->getCookiesString($response);
        return [
            $sessionId,
            $cookies
        ];
    }

    private function realizarLoginComGov(string $sessionId, $login, $senha)
    {
        $loginGovConnector = new LoginGovConnector();
        dump("requisixao autoriza");
        $requestAutorize = new AutorizeGovRequest($sessionId);

        $responseAutorize = $loginGovConnector->send($requestAutorize);

        $novosCookies = $this->getCookiesString($responseAutorize);

        $loginGovConnector->headers()->add('Cookie', $novosCookies);

        preg_match('/authorization_id=(.*)/',
            $responseAutorize->header('location'),
            $regexAutorizacao);

        $inicioLogin = new govInicialRedirectRequest($regexAutorizacao[1]);
        $responseInicialLogin = $loginGovConnector->send($inicioLogin);

        preg_match('/name="_csrf.*value="(.*)\"/', $responseInicialLogin->body(), $regexCsrf);

        $loginGovCpf = new loginGovRequest($regexAutorizacao[1], $regexCsrf[1], $login);
        $responseLoginParte1 = $loginGovConnector->send($loginGovCpf);
        $url = 'https://sso.acesso.gov.br/login?client_id=sicaf.gov.br&authorization_id=' . $regexAutorizacao[1];

        preg_match('/h-captcha.*sitekey=\"(.{1,50})\" data-callback/', $responseLoginParte1->body(), $regexHcaptcha);

        dump('resolvendo capthaa');
        $respostaCaptcha = $this->resolverCaptcha($regexHcaptcha[1], $url);

        $loginGovSenha = new loginSenhaGovRequest($regexAutorizacao[1], $regexCsrf[1], $login, $senha, $respostaCaptcha);

        dump("senha");
        $responseLoginParte2 = $loginGovConnector->send($loginGovSenha);

        $cookiesAposLogin = $this->getCookiesString($responseLoginParte2);

        $loginGovConnector->headers()->add('Cookie', $loginGovConnector->headers()->get('Cookie') . ';'. $cookiesAposLogin);

        if ($responseLoginParte2->status() == 400) {
            return [];
        }

        $requestAutorize = new AutorizeGovRequest($sessionId);
        $responseAutorizeFinal = $loginGovConnector->send($requestAutorize);

        $queryParamsString = explode('?', $responseAutorizeFinal->header('location'))[1];
        $queryParams = [];
        foreach (explode('&', $queryParamsString) as $param) {
            list($key, $value) = explode('=', $param);
            $queryParams[$key] = $value;
        }
        return $queryParams;
    }

    private function resolverCaptcha($sitekey, $url): string
    {


        dump(config('services.two_captcha_key'));
        $solver = new \TwoCaptcha\TwoCaptcha(
            [
                'apiKey' => config('services.two_captcha_key'),
//                'defaultTimeout' => 120
            ]);


        $result = $solver->hcaptcha([
            'sitekey'   => $sitekey,
            'url'       => $url,
        ]);

        return $result->code;
    }
}
