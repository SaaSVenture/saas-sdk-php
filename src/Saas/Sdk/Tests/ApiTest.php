<?php namespace Saas\Sdk\Tests;

/**
 * API Interface Documentation
 *
 * @author Taufan Adhitya <toopay@taufanaditya.com>
 * @package saas/sdk
 */

use Mockery as M;
use PHPUnit_Framework_TestCase;
use Exception;
use Saas\Sdk\Api;
use Saas\Sdk\ResourceObject;

class ApiTest extends PHPUnit_Framework_TestCase
{
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
	 * @return Saas\Sdk\Contracts\ApiInterface
	 */
	public function testInvalidApi($transport)
	{
		return Api::factory('some-key', 's0m3s3cr3t', $transport);
	}

	/**
	 * API Instance
	 *
	 * @depends testTransport
	 * @return Saas\Sdk\Contracts\ApiInterface
	 */
	public function testApi($transport)
	{
		return Api::factory('some-key', 's0m3s3cr3t', $transport);
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