<?php

namespace Drupal\google_sso\Service;
use Drupal\Core\Config\ConfigFactoryInterface;
class GoogleClientService
{
    protected $config;
    public function __construct(ConfigFactoryInterface $configFactory)
    {
        $this->config = $configFactory->get('google_sso.settings');
    }
    public function getClient()
    {
        $client = new \Google_Client();
        $client->setClientId($this
            ->config
            ->get('client_id'));
        $client->setClientSecret($this
            ->config
            ->get('client_secret'));
        $client->setRedirectUri('http://local.drupal10.com/google-sso/callback');
        $client->addScope('email');
        $client->addScope('profile');
        return $client;
    }
}

