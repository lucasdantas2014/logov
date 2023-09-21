<?php

namespace App\Http\Integrations\Sicaf;

use GuzzleHttp\RequestOptions;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class SicafConnector extends Connector
{

    /**
     * The Base URL of the API
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return 'https://www3.comprasnet.gov.br/sicaf-web';
    }

    /**
     * Default headers for every request
     *
     * @return string[]
     */
    protected function defaultHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_2_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.114 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7,pl;q=0.6'
        ];
    }

    /**
     * Default HTTP client options
     *
     * @return string[]
     */
    protected function defaultConfig(): array
    {
        return [
            RequestOptions::VERIFY => false,
            'proxy' => 'http://localhost.charlesproxy.com:8888',
            'allow_redirects' => false,
            'curl' => [
                32 => 393216,#CURLOPT_SSLVERSION => CURL_SSLVERSION_MAX_TLSv1_2,
                //64 => false,#CURLOPT_SSL_VERIFYPEER
                //81 => false#CURLOPT_SSL_VERIFYHOST
//                10228 => ['Proxy-Connection:']
            ],
        ];
    }
}
