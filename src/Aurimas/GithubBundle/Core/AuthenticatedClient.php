<?php

namespace Aurimas\GithubBundle\Core;

use Aurimas\UserBundle\Entity\User;
use Github\Client;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class AuthenticatedClient
 * @package Aurimas\GithubBundle\Core
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class AuthenticatedClient
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @return Client
     */
    public function getClient()
    {
        $token = $this->getTokenStorage()->getToken();

        /** @var User $user */
        $user = $token->getUser();

        if (is_string($user)) {
            return $this->client;
        }

        $this->client->authenticate($user->getToken(), null, Client::AUTH_URL_TOKEN);

        return $this->client;
    }

    /**
     * @param Client $client
     *
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return TokenStorage
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    /**
     * @param TokenStorage $tokenStorage
     *
     * @return $this
     */
    public function setTokenStorage($tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }
}
