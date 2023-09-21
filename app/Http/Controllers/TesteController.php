<?php

namespace App\Http\Controllers;

use App\Http\Integrations\LoginGov\LoginGovConnector;
use App\Http\Integrations\Sicaf\sicafConnector;
use App\Http\Integrations\Sicaf\Requests\AutorizeRequest;
use App\Http\Integrations\Sicaf\Requests\govInicialRedirectRequest;
use App\Http\Integrations\Sicaf\Requests\govRedirect2Request;
use App\Http\Integrations\Sicaf\Requests\govRedirectRequest;
use App\Http\Integrations\Sicaf\Requests\loginGovRequest;
use App\Http\Integrations\Sicaf\Requests\loginSenhaGovRequest;
use App\Http\Integrations\Sicaf\Requests\PaginaInicialRequest;
use App\Http\Integrations\LoginGov\Requests\AutorizeRequest as AutorizeGovRequest;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;

class TesteController extends Controller
{
    public function teste() {

        $sessionId = $this->pegarSessionIdSicaf();

        $this->realizarLoginComGov($sessionId);

        return response()->json(
            [
                'sessionId' => $sessionId,
                'mensagem' => 'login efetuado com sucesso'
            ],
            200);
    }

    private function pegarSessionIdSicaf()
    {
        $paginaInicial = new PaginaInicialRequest();

        $response = $paginaInicial->send();

        preg_match('/(\/sicaf-web\/index.jsf;jsession.*)\" e/',
            $response->body(),
            $resultadoRegexButton);

        preg_match('/id.?="javax.faces.ViewState".*value="(.*)\" a/',
            $response->body(),
            $resultadoJavaxView);

        $url = $resultadoRegexButton[1];

        preg_match('/jsessionid=(.*)/', $url, $resultadoJsessionId);
        $sessionId = str_replace(['.', '_'], '', $resultadoJsessionId[1]);

        return $sessionId;
//        $response = $client->post('https://www3.comprasnet.gov.br' . $url, [
//            'form' => [
//                'avax.faces.partial.ajax' => 'true',
//                'javax.faces.source' => 'formLogin',
//                'javax.faces.partial.execute' => '@all',
//                'formLogin:btnEntrarSsoFornecedor' => 'formLogin:btnEntrarSsoFornecedor',
//                'formLogin' => 'formLogin',
//                'formLogin:tipoUsuario_focus' => '',
//                'formLogin:tipoUsuario_input' => '',
//                'javax.faces.ViewState' => $resultadoJavaxView[1]
//            ]
//        ]);
    }

    private function realizarLoginComGov(string $sessionId)
    {
        $autorize = new AutorizeRequest(sessionId: $sessionId);

        $responseAutorize = $autorize->send();

        $novosCookies = $responseAutorize->header('set-cookie');

        $cookieSession = explode('=', explode(';', $novosCookies[0])[0])[1];
        $cookieIngression = explode('=', explode(';', $novosCookies[1])[0])[1];

        preg_match('/authorization_id=(.*)/', $responseAutorize->header('location'), $regexAutorizacao);

        $inicioLogin = new govInicialRedirectRequest($regexAutorizacao[1]);
        $inicioLogin->headers()->add('Cookie', 'Session_Gov_Br_Prod=' . $cookieSession .";INGRESSCOOKIE=" . $cookieIngression);
        $responseInicialLogin = $inicioLogin->send();

        preg_match('/name="_csrf.*value="(.*)\"/', $responseInicialLogin->body(), $regexCsrf);

        $loginGov = new loginGovRequest($regexAutorizacao[1], $regexCsrf[1], '133.908.814-25', $cookieSession, $cookieIngression);

        $loginGov->headers()->add('Cookie', 'Session_Gov_Br_Prod=' . $cookieSession .";INGRESSCOOKIE=" . $cookieIngression);
        $responseLoginParte1 = $loginGov->send();

        $solver = new \TwoCaptcha\TwoCaptcha('3eb2cb046db80c84bb51c1a02499b626');

        preg_match('/h-captcha.*sitekey=\"(.{1,50})\" data-callback/', $responseLoginParte1->body(), $regexHcaptcha);

        $result = $solver->hcaptcha([
            'sitekey'   => $regexHcaptcha[1],
            'url'       => 'https://sso.acesso.gov.br',
        ]);

        $loginGov = new loginSenhaGovRequest($regexAutorizacao[1], $regexCsrf[1], '133.908.814-25', '1234Fr@n!', $result->code);

        $loginGov->headers()->add('Cookie', 'Session_Gov_Br_Prod=' . $cookieSession .";INGRESSCOOKIE=" . $cookieIngression);
        $responseLoginParte2 = $loginGov->send();

        $redirect = new govRedirectRequest();

        $redirect->headers()->add('Cookie', 'Session_Gov_Br_Prod=' . $cookieSession .";INGRESSCOOKIE=" . $cookieIngression);
        $redirect->send();

        $redirect2 = new govRedirect2Request();

        $redirect2->headers()->add('Cookie', 'Session_Gov_Br_Prod=' . $cookieSession .";INGRESSCOOKIE=" . $cookieIngression);
        $responseLoginParte4 = $redirect2->send();

        dump($responseLoginParte4->headers());
    }

    private function realizarLoginComGovConnector(string $sessionId)
    {
        $loginGovConnector = new LoginGovConnector();
        $requestAutorize = new AutorizeGovRequest($sessionId);

        $responseAutorize = $loginGovConnector->send($requestAutorize);

        $novosCookies = $responseAutorize->header('set-cookie');

        $cookieSession = explode('=', explode(';', $novosCookies[0])[0])[1];
        $cookieIngression = explode('=', explode(';', $novosCookies[1])[0])[1];

        preg_match('/authorization_id=(.*)/',
            $responseAutorize->header('location'),
            $regexAutorizacao);

        $inicioLogin = new govInicialRedirectRequest($regexAutorizacao[1]);
        $inicioLogin->headers()->add('Cookie', 'Session_Gov_Br_Prod=' . $cookieSession .";INGRESSCOOKIE=" . $cookieIngression);
        $responseInicialLogin = $inicioLogin->send();

        preg_match('/name="_csrf.*value="(.*)\"/', $responseInicialLogin->body(), $regexCsrf);

        $loginGov = new loginGovRequest($regexAutorizacao[1], $regexCsrf[1], '133.908.814-25', $cookieSession, $cookieIngression);

        $loginGov->headers()->add('Cookie', 'Session_Gov_Br_Prod=' . $cookieSession .";INGRESSCOOKIE=" . $cookieIngression);
        $responseLoginParte1 = $loginGov->send();

        $solver = new \TwoCaptcha\TwoCaptcha('3eb2cb046db80c84bb51c1a02499b626');

        preg_match('/h-captcha.*sitekey=\"(.{1,50})\" data-callback/', $responseLoginParte1->body(), $regexHcaptcha);

        $result = $solver->hcaptcha([
            'sitekey'   => $regexHcaptcha[1],
            'url'       => 'https://sso.acesso.gov.br',
        ]);

        $loginGov = new loginSenhaGovRequest($regexAutorizacao[1], $regexCsrf[1], '133.908.814-25', '1234Fr@n!', $result->code);

        $loginGov->headers()->add('Cookie', 'Session_Gov_Br_Prod=' . $cookieSession .";INGRESSCOOKIE=" . $cookieIngression);
        $responseLoginParte2 = $loginGov->send();

        return $responseLoginParte2;

//        $redirect = new govRedirectRequest();
//
//        $redirect->headers()->add('Cookie', 'Session_Gov_Br_Prod=' . $cookieSession .";INGRESSCOOKIE=" . $cookieIngression);
//        $redirect->send();
//
//        $redirect2 = new govRedirect2Request();
//
//        $redirect2->headers()->add('Cookie', 'Session_Gov_Br_Prod=' . $cookieSession .";INGRESSCOOKIE=" . $cookieIngression);
//        $responseLoginParte4 = $redirect2->send();
//
//        dump($responseLoginParte4->headers());
    }
}
