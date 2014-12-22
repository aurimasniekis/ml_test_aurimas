<?php

namespace Aurimas\RepositoriesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $client = $this->get('aurimas_github.authenticated.client')->getClient();
        $issuesStatsService = $this->get('aurimas_issues.stats.service');

        $repositories = $client->me()->repositories();
        $stats = $issuesStatsService->loadStatsForRepositories($repositories);

        return $this->render(
            'AurimasRepositoriesBundle:Default:index.html.twig',
            [
                'repositories' => $repositories,
                'stats' => $stats
            ]
        );
    }
}
