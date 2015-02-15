<?php namespace Saas\Sdk;

/**
 * Main API
 *
 * @author Taufan Adhitya <toopay@taufanaditya.com>
 * @package saas/sdk
 */

use Saas\Sdk\Contracts\ApiInterface;
use Saas\Sdk\Contracts\TransportInterface;
use Saas\Sdk\Transports\LocalTransport;
use Saas\Sdk\Transports\RemoteTransport;
use Saas\Sdk\Credential;
use Exception, RuntimeException;

final class Api implements ApiInterface
{
	/**
	 * API  transport
	 *
	 * @var Saas\Sdk\Contracts\TransportInterface
	 */
	private $transport;

	/**
	 * Main API factory
	 *
	 * @param string API Key
	 * @param string API Secret
	 * @return Saas\Sdk\Contracts\ApiInterface
	 */
	final public static function factory($key, $secret, $transport = null)
	{
		return new static($key, $secret, $transport);
	}

	/**
	 * Constructor
	 *
	 * @param string API Key
	 * @param string API Secret
	 */
	public function __construct($key, $secret, TransportInterface $transport = null)
	{
		$credential = new Credential($key, $secret);
		$this->transport = $transport;

		// Pick appropriate transport, if it wasn't provided
		// @codeCoverageIgnoreStart
		if (!$this->transport) {
			if (strpos($_SERVER['HTTP_HOST'], TransportInterface::SAAS_API_ROOT) !== false) {
				$this->transport = new LocalTransport($credential);
			} else {
				$this->transport = new RemoteTransport($credential);
			}
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * @{inheritDoc}
	 */
	public function getOriginalAppUrl()
	{
		try {
			$app = $this->transport->getOwnerApp();
		} catch (Exception $e) {
			throw new RuntimeException($e->getMessage());
		}

		return self::SAAS_API_HTTP_SCHEME.$app->url;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getLoginUrl()
	{
		try {
			$app = $this->transport->getOwnerApp();
		} catch (Exception $e) {
			throw new RuntimeException($e->getMessage());
		}

		return self::SAAS_API_HTTP_SCHEME
					.$app->slug
					.TransportInterface::SAAS_API_DOMAIN_SEPARATOR
					.TransportInterface::SAAS_API_ROOT
					.'/auth/login';
	}
}