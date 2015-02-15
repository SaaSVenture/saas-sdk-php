<?php namespace Saas\Sdk\Tests;

/**
 * API Interface Documentation
 *
 * @author Taufan Adhitya <toopay@taufanaditya.com>
 * @package saas/sdk
 */

use \Mockery as M;
use PHPUnit_Framework_TestCase;
use Saas\Sdk\Api;
use Saas\Sdk\ResourceObject;

class ApiTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Transport Mock
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
	 * API Instance
	 *
	 * @depends testTransport
	 * @return Saas\Sdk\Contracts\ApiInterface
	 */
	public function testApi($transport)
	{
		$_SERVER['HTTP_HOST'] = 'foo.com';
		return Api::factory('some-key', 's0m3s3cr3t', $transport);
	}

	/**
	 * Original App Getter test
	 *
	 * @depends testApi
	 */
	public function testGetOriginalAppUrl($api)
	{
		$this->assertEquals('http://foo.com', $api->getOriginalAppUrl());
	}

	/**
	 * Instance Auth URL Getter test
	 *
	 * @depends testApi
	 */
	public function testGetLoginUrl($api)
	{
		$this->assertEquals('http://foo.saasapi.com/auth/login', $api->getLoginUrl());
	}

}