<?php
namespace Leochenftw\eCommerce\eCollector\API;

use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use Leochenftw\Debugger;

class POLi
{
    public static function initiate($amount, $ref)
    {
        $gateway_endpoint   =   Config::inst()->get('Leochenftw\eCommerce\eCollector', 'API')['POLi'] . '/Initiate';
        $settings           =   Config::inst()->get('Leochenftw\eCommerce\eCollector', 'GatewaySettings')['POLi'];

        $cert_path          =   $settings['CERT'];
        $client_code        =   $settings['CLIENTCODE'];
        $auth_code          =   $settings['AUTHCODE'];
        $home               =   Config::inst()->get('Leochenftw\eCommerce\eCollector', 'MerchantSettings')['MerchantHomepageURL'];
        $returnurl          =   Director::absoluteBaseURL() . 'e-collector/poli-complete';

        $json_builder       =   '{
                                    "Amount":"' . $amount . '",
                                    "CurrencyCode":"NZD",
                                    "MerchantReference":"' . $ref . '",
                                    "MerchantHomepageURL":"' . $home . '",
                                    "SuccessURL":"' . $returnurl . '",
                                    "FailureURL":"' . $returnurl . '",
                                    "CancellationURL":"' . $returnurl . '",
                                    "NotificationURL":"' . $returnurl . '"
                                }';

        $auth               =   base64_encode($client_code . ':' . $auth_code);

        $header             =   [
                                    'Content-Type: application/json',
                                    'Authorization: Basic ' . $auth
                                ];

         $ch = curl_init($gateway_endpoint);

         //See the cURL documentation for more information: http://curl.haxx.se/docs/sslcerts.html
         //We recommend using this bundle: https://raw.githubusercontent.com/bagder/ca-bundle/master/ca-bundle.crt

         curl_setopt( $ch, CURLOPT_CAINFO, $cert_path);
         curl_setopt( $ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
         curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
         curl_setopt( $ch, CURLOPT_HEADER, 0);
         curl_setopt( $ch, CURLOPT_POST, 1);
         curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_builder);
         curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0);
         curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

         $response          =   curl_exec( $ch );

         curl_close ($ch);

         return $response;
    }

    public static function process($amount, $ref)
    {
        $response           =   self::initiate($amount, $ref);
        return json_decode($response, true);;
    }

    public static function fetch($token)
    {
        $gateway_endpoint   =   Config::inst()->get('Leochenftw\eCommerce\eCollector', 'API')['POLi'];
        $settings           =   Config::inst()->get('Leochenftw\eCommerce\eCollector', 'GatewaySettings')['POLi'];

        $cert_path          =   $settings['CERT'];
        $client_code        =   $settings['CLIENTCODE'];
        $auth_code          =   $settings['AUTHCODE'];

        $auth               =   base64_encode($client_code . ':' . $auth_code);
        $header             =   ['Authorization: Basic '.$auth];

        $ch                 =   curl_init($gateway_endpoint . '?token=' . $token);
        $ch                 =   curl_init("$gateway_endpoint/GetTransaction?token=" . urlencode($token));

        //See the cURL documentation for more information: http://curl.haxx.se/docs/sslcerts.html
        //We recommend using this bundle: https://raw.githubusercontent.com/bagder/ca-bundle/master/ca-bundle.crt

        curl_setopt( $ch, CURLOPT_CAINFO, $cert_path );
        curl_setopt( $ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2 );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_POST, 0 );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

        $response           =   curl_exec( $ch );

        curl_close( $ch );

        $json               =   json_decode( $response, true );

        return $json;
    }
}
