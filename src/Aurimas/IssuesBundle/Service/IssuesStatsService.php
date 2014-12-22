<?php

namespace Aurimas\IssuesBundle\Service;

use Aurimas\GithubBundle\Core\AuthenticatedClient;
use Github\Client;

/**
 * Class IssuesStatsService
 * @package Aurimas\IssuesBundle\Service
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class IssuesStatsService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param AuthenticatedClient $authenticatedClient
     *
     * @return $this
     */
    public function setClient($authenticatedClient)
    {
        $this->client = $authenticatedClient->getClient();

        return $this;
    }

    /**
     * @param array $repositories
     *
     * @return array
     */
    public function loadStatsForRepositories($repositories)
    {
        $stats = [];
        $client = $this->getClient();

        foreach ($repositories as $repository) {
            $issues = $client->issues()->all(
                $repository['owner']['login'],
                $repository['name'],
                [
                    'filter' => 'all',
                    'state' => 'all'
                ]
            );

            $stats[$repository['id']] = $this->countStates($issues);
        }

        return $stats;
    }

    /**
     * @param array $issues
     *
     * @return array
     */
    protected function countStates($issues)
    {
        $stats = [];
        $stats['open'] = 0;
        $stats['closed'] = 0;

        for ($i = 0; $i < sizeof($issues); $i++) {
            switch ($issues[$i]['state']) {
                case 'closed':
                    $stats['closed']++;
                    break;

                case 'open':
                    $stats['open']++;
                    break;
            }
        }

        return $stats;
    }

}
