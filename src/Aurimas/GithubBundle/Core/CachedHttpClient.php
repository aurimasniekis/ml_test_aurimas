<?php

namespace Aurimas\GithubBundle\Core;

use Aurimas\GitHubBundle\Cache\RedisDriver;
use Github\HttpClient\Cache\FilesystemCache;
use Github\HttpClient\CachedHttpClient as OldCachedHttpClient;

/**
 * Class CachedHttpClient
 * @package Aurimas\GithubBundle\Core
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class CachedHttpClient extends OldCachedHttpClient
{
    /**
     * @return RedisDriver
     */
    public function getCache()
    {
        return $this->cache;
    }

    protected function createRequest(
        $httpMethod,
        $path,
        $body = null,
        array $headers = array(),
        array $options = array()
    ) {
        $request = parent::createRequest($httpMethod, $path, $body, $headers, $options);

        $cache = $this->getCache()->createRequestChecked($path);

        $modifiedAt = $cache[0];
        $eTag = $cache[1];

        if ($modifiedAt) {
            $modifiedAt = new \DateTime('@' . $modifiedAt);
            $modifiedAt->setTimezone(new \DateTimeZone('GMT'));

            $request->addHeader(
                'If-Modified-Since',
                sprintf('%s GMT', $modifiedAt->format('l, d-M-y H:i:s'))
            );
        }
        if ($eTag) {
            $request->addHeader(
                'If-None-Match',
                $eTag
            );
        }

        return $request;
    }

}
