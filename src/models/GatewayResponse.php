<?php

namespace Leochenftw\eCommerce\eCollector;
use Leochenftw\eCommerce\eCollector\API\Paystation;
use Leochenftw\eCommerce\eCollector\API\POLi;
use Leochenftw\eCommerce\eCollector\API\DPS;
use Leochenftw\Debugger;

class GatewayResponse
{
    private $uri;
    private $error;

    public function __construct(String $gateway, Array $response)
    {
        if ($gateway == POLi::class) {
            if (!empty($response['Success'])) {
                $this->uri      =   $response['NavigateURL'];
            } else {
                $this->error    =   $response['ErrorMessage'];
            }
        }

        if ($gateway == DPS::class) {
            if (!empty($response['URI'])) {
                $this->uri      =   $response['URI'];
            } elseif (!empty($response['ResponseText'])) {
                $this->error    =   $response['ResponseText'];
            }
        }

        if ($gateway == Paystation::class) {
            if (!empty($response['InitiationRequestResponse']['DigitalOrder'])) {
                $this->uri      =   $response['InitiationRequestResponse']['DigitalOrder'];
            } elseif (!empty($response['response']['em'])) {
                $this->error    =   $response['response']['em'];
            }
        }
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }

        return $this;
    }
}
