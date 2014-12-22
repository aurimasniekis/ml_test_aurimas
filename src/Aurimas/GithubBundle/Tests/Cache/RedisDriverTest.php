<?php

namespace Aurimas\GithubBundle\Tests\Cache;

use Aurimas\GitHubBundle\Cache\RedisDriver;
use Guzzle\Http\Message\Response;
use Predis\Client;

class RedisDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function dataHas()
    {
        $out = [];

        $out[] = ['foo', 'github_foo', 1, true];
        $out[] = ['bar', 'github_bar', 0, false];

        return $out;
    }

    /**
     * @dataProvider dataHas
     *
     * @param string $id
     * @param string $key
     * @param integer $output
     * @param bool $expectation
     */
    public function testHas($id, $key, $output, $expectation)
    {
        $redis = $this->getRedisMock(['exists']);

        $driver = new RedisDriver();
        $driver->setRedis($redis);


        $redis->expects($this->once())
            ->method('exists')
            ->with($key)
            ->willReturn($output);

        $result = $driver->has($id);

        $this->assertEquals($expectation, $result);
    }

    /**
     * @return array
     */
    public function dataModifiedSince()
    {
        $out = [];

        $time = time();

        $out[] = ['foo', 'github_foo:time', $time, $time];
        $out[] = ['bar', 'github_bar:time', null, null];

        return $out;
    }

    /**
     * @dataProvider dataModifiedSince
     *
     * @param string $id
     * @param string $key
     * @param int $output
     * @param int $expectation
     */
    public function testModifiedSince($id, $key, $output, $expectation)
    {
        $redis = $this->getRedisMock(['get']);

        $driver = new RedisDriver();
        $driver->setRedis($redis);


        $redis->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($output);

        $result = $driver->getModifiedSince($id);

        $this->assertEquals($expectation, $result);
    }

    /**
     * @return array
     */
    public function dataETag()
    {
        $out = [];

        $out[] = ['foo', 'github_etag_foo', '12345', '12345'];
        $out[] = ['bar', 'github_etag_bar', null, null];

        return $out;
    }

    /**
     * @dataProvider dataETag
     *
     * @param string $id
     * @param string $key
     * @param int $output
     * @param int $expectation
     */
    public function testETag($id, $key, $output, $expectation)
    {
        $redis = $this->getRedisMock(['get']);

        $driver = new RedisDriver();
        $driver->setRedis($redis);


        $redis->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($output);

        $result = $driver->getETag($id);

        $this->assertEquals($expectation, $result);
    }

    public function testGetDoesNotExistKey()
    {
        $redis = $this->getRedisMock(['get']);

        $driver = new RedisDriver();
        $driver->setRedis($redis);

        $id = 'foo';
        $key = 'github_' . $id;

        $redis->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $this->setExpectedException(
            '\\InvalidArgumentException',
            'Resource with "foo" does not exists'
        );

        $driver->get($id);
    }

    public function testGetInvalidObject()
    {
        $redis = $this->getRedisMock(['get']);

        $driver = new RedisDriver();
        $driver->setRedis($redis);

        $id = 'foo';
        $key = 'github_' . $id;

        $redis->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(serialize(new \stdClass()));

        $this->setExpectedException(
            '\\InvalidArgumentException',
            'Cache result is not instance of "Response" instead "stdClass" given for key "github_foo"'
        );

        $driver->get($id);
    }

    public function testGet()
    {
        $redis = $this->getRedisMock(['get']);

        $driver = new RedisDriver();
        $driver->setRedis($redis);

        $id = 'foo';
        $key = 'github_' . $id;

        $response = new Response(200);

        $redis->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(serialize($response));

        $result = $driver->get($id);

        $this->assertEquals($response->serialize(), $result->serialize());
    }

    public function testSet()
    {
        $redis = $this->getRedisMock(['pipeline']);

        $driver = new RedisDriver();
        $driver->setRedis($redis);

        $id = 'foo';
        $key = 'github_' . $id;
        $keyETag = 'github_etag_' . $id;
        $keyTime = 'github_' . $id . ':time';

        $response = new Response(200);
        $response->setHeader('ETag', '12345');

        $pipelineContext = $this->getMockBuilder('Predis\Pipeline\PipelineContext')
            ->disableOriginalConstructor()
            ->setMethods(['set', 'execute'])
            ->getMock();

        $redis->expects($this->once())
            ->method('pipeline')
            ->willReturn($pipelineContext);

        $pipelineContext->expects($this->at(0))
            ->method('set')
            ->with($key, serialize($response))
            ->willReturn($pipelineContext);

        $pipelineContext->expects($this->at(1))
            ->method('set')
            ->with($keyETag, '12345')
            ->willReturn($pipelineContext);

        $pipelineContext->expects($this->at(2))
            ->method('set')
            ->with($keyTime, $this->lessThanOrEqual(time()))
            ->willReturn($pipelineContext);

        $pipelineContext->expects($this->at(3))
            ->method('execute');

        $driver->set($id, $response);
    }

    /**
     * @param $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|Client
     */
    protected function getRedisMock($methods = [])
    {
        $redis = $this->getMockBuilder('\Predis\Client')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $redis;
    }
}
