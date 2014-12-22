<?php

namespace Aurimas\GitHubBundle\Cache;

use Github\HttpClient\Cache\CacheInterface;
use Guzzle\Http\Message\Response;
use Predis\Client;

/**
 * Class RedisDriver
 * @package Aurimas\GithubBundle\Core
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class RedisDriver implements CacheInterface
{
    const KEY_PREFIX = 'github_';

    /**
     * @var Client
     */
    protected $redis;

    /**
     * @return Client
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * @param Client $redis
     *
     * @return $this
     */
    public function setRedis($redis)
    {
        $this->redis = $redis;

        return $this;
    }

    /**
     * @param string $id The id of the cached resource
     *
     * @return bool if present
     */
    public function has($id)
    {
        $key = $this->buildKey($id);

        return (bool)$this->getRedis()->exists($key);
    }

    /**
     * @param string $id The id of the cached resource
     *
     * @return null|integer The modified since timestamp
     */
    public function getModifiedSince($id)
    {
        $key = $this->buildKey($id) . ':time';

        return $this->getRedis()->get($key);
    }

    /**
     * @param string $id The id of the cached resource
     *
     * @return null|string The ETag value
     */
    public function getETag($id)
    {
        $key = $this->buildKey($id, true);

        $resource = $this->getRedis()->get($key);

        return $resource;
    }

    /**
     * @param string $id The id of the cached resource
     *
     * @return Response The cached response object
     *
     * @throws \InvalidArgumentException If cache data don't exists
     */
    public function get($id)
    {
        $key = $this->buildKey($id);
        $resource = $this->getRedis()->get($key);

        if (!$resource) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Resource with "%s" does not exists',
                    $id
                )
            );
        }

        $response = unserialize($resource);

        if (!$response instanceof Response) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Cache result is not instance of "Response" instead "%s" given for key "%s"',
                    get_class($response),
                    $key
                )
            );
        }

        return $response;
    }

    /**
     * @param string $id The id of the cached resource
     * @param Response $response The response to cache
     *
     * @throws \InvalidArgumentException If cache data cannot be saved
     */
    public function set($id, Response $response)
    {
        $key = $this->buildKey($id);
        $keyETag = $this->buildKey($id, true);
        $keyTime = $key . ':time';

        $resource = serialize($response);
        $eTag = $response->getHeader('ETag');
        $time = time();

        $this->getRedis()->pipeline()
            ->set($key, $resource)
            ->set($keyETag, $eTag)
            ->set($keyTime, $time)
            ->execute();
    }

    /**
     * @param $path
     *
     * @return array
     */
    public function createRequestChecked($path)
    {
        $keyETag = $this->buildKey($path, true);
        $keyTime = $this->buildKey($path) . ':time';

        return $this->getRedis()->pipeline()->get($keyTime)->get($keyETag)->execute();
    }

    /**
     * @param string $id
     * @param bool $eTag
     * @return string
     */
    protected function buildKey($id, $eTag = false)
    {
        return static::KEY_PREFIX . ($eTag ? 'etag_' : '') . $id;
    }
}
