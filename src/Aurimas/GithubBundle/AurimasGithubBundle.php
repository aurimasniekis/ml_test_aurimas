<?php

namespace Aurimas\GithubBundle;

use Aurimas\GithubBundle\DependencyInjection\Compiler\ParseConfigPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AurimasGithubBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ParseConfigPass());
    }
}
