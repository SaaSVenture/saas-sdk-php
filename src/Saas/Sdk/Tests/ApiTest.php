<?php namespace Saas\Sdk\Tests;

/**
 * API Interface Documentation
 *
 * @author Taufan Adhitya <toopay@taufanaditya.com>
 * @package saas/sdk
 */

use Saas\Sdk\Api;
use Saas\Sdk\ResourceObject;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

use Mockery as M;
use PHPUnit_Framework_TestCase;
use Exception;

class ApiTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Session provider Mock
	 *
	 * @return Symfony\Component\HttpFoundation\Session\SessionInterface
	 */
	public function testSession()
	{
		return new Session(new MockArraySessionStorage());
	}

	/**
	 * Invalid Transport Mock
	 *
	 * @return Saas\Sdk\Contracts\TransportInterface
	 */
	public function testInvalidTransport()
	{
		$mock = M::mock('Saas\\Sdk\\Contracts\\TransportInterface');
		$mock->shouldReceive('getOwnerApp')->once()->andThrow(new Exception('Just wrong!'));

		return $mock;
	}

	/**
	 * Valid Transport Mock
	 *
	 * @return Saas\Sdk\Contracts\TransportInterface
	 */
	public function testTransport()
	{
		$mock = M::mock('Saas\\Sdk\\Contracts\\TransportInterface');
		$mock->shouldReceive('getOwnerApp')->once()->andReturn(new ResourceObject(array(
			'url' => 'foo.com',
			'slug' => 'foo'
		)));

		return $mock;
	}

	/**
	 * Mock invalid API Instance
	 *
	 * @depends testInvalidTransport
	 * @depends testSession
	 * @return Saas\Sdk\Contracts\ApiInterface
	 */
	public function testInvalidApi($transport, $session)
	{
		return Api::factory('some-key', 's0m3s3cr3t', $transport, $session);
	}

	/**
	 * API Instance
	 *
	 * @depends testTransport
	 * @depends testSession
	 * @return Saas\Sdk\Contracts\ApiInterface
	 */
	public function testApi($transport, $session)
	{
		return Api::factory('some-key', 's0m3s3cr3t', $transport, $session);
	}

	/**
	 * Original App Getter test
	 *
	 * @depends testInvalidApi
	 * @depends testApi
	 */
	public function testGetOriginalAppUrl($invalidApi, $api)
	{
		// Valid API execution
		$this->assertEquals('http://foo.com', $api->getOriginalAppUrl());

		// Invalid API execution
		$this->setExpectedException('Exception', 'Just wrong!');
		$invalidApi->getOriginalAppUrl();
	}

	/**
	 * Instance Auth URL Getter test
	 *
	 * @depends testInvalidApi
	 * @depends testApi
	 */
	public function testGetLoginUrl($invalidApi, $api)
	{
		$this->assertEquals('http://foo.saasapi.com/auth/login', $api->getLoginUrl());

		// Invalid API execution
		$this->setExpectedException('Exception', 'Just wrong!');
		$invalidApi->getLoginUrl();
	}

}