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
	 * API credential
	 *
	 * @var Saas\Sdk\Credential
	 */
	private $credential;

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
		$this->credential = new Credential($key, $secret);
		$this->transport = $transport;

		// Pick appropriate transport, if it wasn't provided
		// @codeCoverageIgnoreStart
		if (!$this->transport) {
			if (strpos($_SERVER['HTTP_HOST'], TransportInterface::SAAS_API_ROOT) !== false) {
				$this->transport = new LocalTransport($this->credential);
			} else {
				$this->transport = new RemoteTransport($this->credential);
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

	/**
	 * @{inheritDoc}
	 */
	public function getExchangeUrl($userId = null, $companyId = null)
	{
		$payload = array('key' => $this->credential->getKey(), 'secret' => $this->credential->getSecret());
		if (!empty($userId) && !empty($companyId)) {
			$payload['user_id'] = $userId;
			$payload['company_id'] = $companyId;
		}

		return self::SAAS_API_HTTP_SCHEME
				.TransportInterface::SAAS_API_ROOT
				.'/exchange?'.http_build_query($payload);
	}
}