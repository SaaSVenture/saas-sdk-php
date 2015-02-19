<?php namespace Saas\Sdk\Tests;

/**
 * API Interface Documentation
 *
 * @author Taufan Adhitya <toopay@taufanaditya.com>
 * @package saas/sdk
 */

use Saas\Sdk\Contracts\ApiInterface;
use Saas\Sdk\Api;
use Saas\Sdk\ResourceObject;
use Saas\Sdk\ResourceCollection;

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
		$mock->shouldReceive('getUser')->once()->andReturn(new ResourceObject(array(
			'name' => 'Mr. Foo',
			'email' => 'foo@foo.com'
		)));
		$mock->shouldReceive('getCompany')->once()->andReturn(new ResourceObject(array(
			'title' => 'FooCorp',
		)));
		$mock->shouldReceive('clearSession')->once();
		$mock->shouldReceive('getPlans')->once()->andReturn(new ResourceCollection(array(
			array('name' => 'Free', 'price' => '0'),
			array('name' => 'Startup', 'price' => '100'),
			array('name' => 'Enterprise', 'price' => '500'),
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
	 * API Callback mock
	 *
	 * @void string
	 */
	public function uselessCallback()
	{
		echo 'yay!';
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

	/**
	 * Instance Exchange URL Getter test
	 *
	 * @depends testApi
	 */
	public function testGetExchangeUrl($api)
	{
		$expectedExchangeUrl = 'http://saasapi.com/exchange?key=some-key&secret=s0m3s3cr3t&user_id=1&company_id=2&session_id=3';
		$this->assertEquals($expectedExchangeUrl, $api->getExchangeUrl(1,2,3));
	}

	/**
	 * Instance checkSession test
	 *
	 * @depends testApi
	 */
	public function testCheckSession($api)
	{
		// Emulate accepting hash
		$_GET[ApiInterface::SAAS_API_HASH] = md5('some-key');
		$_GET[ApiInterface::SAAS_API_QS_USER] = '1';
		$_GET[ApiInterface::SAAS_API_QS_COMPANY] = '2';
		$_GET[ApiInterface::SAAS_API_QS_SESSION] = '3';

		$callableOne = array($this, 'uselessCallback');
		$callableTwo = function() {
			echo 'Look ma!';
		};

		ob_start();
		$api->checkSession($callableOne);
		$callbackContentOne = ob_get_clean();
		$this->assertEquals('yay!', $callbackContentOne);

		ob_start();
		$api->checkSession($callableTwo);
		$callbackContentTwo = ob_get_clean();
		$this->assertEquals('Look ma!', $callbackContentTwo);
	}

	/**
	 * Login checker and Logout test
	 *
	 * @depends testApi
	 */
	public function testIsLoginLogout($api)
	{
		$api->logout();
		$this->assertFalse($api->isLogin());

		// Emulate accepting hash
		$_GET[ApiInterface::SAAS_API_HASH] = md5('some-key');
		$_GET[ApiInterface::SAAS_API_QS_USER] = '1';
		$_GET[ApiInterface::SAAS_API_QS_COMPANY] = '2';
		$_GET[ApiInterface::SAAS_API_QS_SESSION] = '3';
		$api->checkSession();

		$this->assertTrue($api->isLogin());
	}

	/**
	 * Get User Resource test
	 *
	 * @depends testApi
	 */
	public function testGetUser($api)
	{
		$user = $api->getActiveUser();

		$this->assertInstanceOf('Saas\Sdk\ResourceObject', $user);
		$this->assertEquals('Mr. Foo', $user['name']);
		$this->assertEquals('foo@foo.com', $user['email']);
	}

	/**
	 * Get Company Resource test
	 *
	 * @depends testApi
	 */
	public function testGetCompany($api)
	{
		$company = $api->getActiveCompany();

		$this->assertInstanceOf('Saas\Sdk\ResourceObject', $company);
		$this->assertEquals('FooCorp', $company['title']);
	}

	/**
	 * Get plans
	 *
	 * @depends testApi
	 */
	public function testGetPlans($api)
	{
		$plans = $api->getPlans();

		$this->assertInstanceOf('Saas\Sdk\ResourceCollection', $plans);

		foreach ($plans as $i => $plan) {
			switch ($i) {
				case 0:
					$this->assertEquals('Free',$plan['name']);
					$this->assertEquals(0,$plan['price']);
					break;
				case 1:
					$this->assertEquals('Startup',$plan['name']);
					$this->assertEquals(100,$plan['price']);
					break;
				case 3:
					$this->assertEquals('Enterprise',$plan['name']);
					$this->assertEquals(500,$plan['price']);
					break;
			}
		}
	}
}