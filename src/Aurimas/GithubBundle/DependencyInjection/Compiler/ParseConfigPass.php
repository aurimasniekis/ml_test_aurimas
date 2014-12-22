<?php

namespace Aurimas\GithubBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ParseConfigPass
 * @package Aurimas\GithubBundle\DependencyInjection\Compiler
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class ParseConfigPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $useRedisCache = $container->getParameter('aurimas_github.use_redis_cache');

        $gitHubClientDefinition = new Definition();
        $gitHubClientDefinition->setClass($container->getParameter('aurimas_github.client.class'));

        if ($useRedisCache) {
            // RedisDriver definition
            $redisCacheDriverDefinition = new Definition();
            $redisCacheDriverDefinition->setClass($container->getParameter('aurimas_github.cache.redis.driver.class'));

            $redisConnectionId = 'snc_redis.' . $container->getParameter('aurimas_github.redis_connection');
            $redisCacheDriverDefinition->addMethodCall('setRedis', [new Reference($redisConnectionId)]);

            $redisCacheDriverId = 'aurimas_github.cache.redis.driver';
            $container->setDefinition($redisCacheDriverId, $redisCacheDriverDefinition);


            // CachedHttpClient definition
            $cachedHttpClientDefinition = new Definition();
            $cachedHttpClientDefinition->setClass($container->getParameter('aurimas_github.cached.http.client.class'));
            $cachedHttpClientDefinition->addMethodCall('setCache', [new Reference($redisCacheDriverId)]);

            $cachedHttpClientId = 'aurimas_github.cached.http.client';
            $container->setDefinition($cachedHttpClientId, $cachedHttpClientDefinition);

            $gitHubClientDefinition->addArgument(new Reference($cachedHttpClientId));
        }

        $container->setDefinition('aurimas_github.client', $gitHubClientDefinition);
    }

}
