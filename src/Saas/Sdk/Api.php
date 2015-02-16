<?php namespace Saas\Sdk;

/**
 * Main API
 *
 * @author Taufan Adhitya <toopay@taufanaditya.com>
 * @package saas/sdk
 */

use Saas\Sdk\Contracts\ApiInterface;
use Saas\Sdk\Contracts\TransportInterface;
use Saas\Sdk\Transports\AbstractTransport;
use Saas\Sdk\Transports\LocalTransport;
use Saas\Sdk\Transports\RemoteTransport;
use Saas\Sdk\Credential;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
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
	 * API session
	 *
	 * @var Symfony\Component\HttpFoundation\Session\SessionInterface
	 */
	private $session;

	/**
	 * Main API factory
	 *
	 * @param string API Key
	 * @param string API Secret
	 * @param Saas\Sdk\TransportInterface
	 * @param Symfony\Component\HttpFoundation\Session\SessionInterface
	 * @return Saas\Sdk\Contracts\ApiInterface
	 */
	final public static function factory($key, $secret, TransportInterface $transport = null, SessionInterface $session = null)
	{
		return new static($key, $secret, $transport, $session);
	}

	/**
	 * Constructor
	 *
	 * @param string API Key
	 * @param string API Secret
	 * @param Saas\Sdk\TransportInterface
	 * @param Symfony\Component\HttpFoundation\Session\SessionInterface
	 */
	public function __construct($key, $secret, TransportInterface $transport = null, SessionInterface $session = null)
	{
		// Set credential
		$this->credential = new Credential($key, $secret);

		// Set transport
		$this->transport = $transport;
		// Pick appropriate transport, if it wasn't provided
		// @codeCoverageIgnoreStart
		if (!$this->transport) {
			if (strpos($_SERVER['HTTP_HOST'], AbstractTransport::getApiRoot()) !== false) {
				$this->transport = new LocalTransport($this->credential);
			} else {
				$this->transport = new RemoteTransport($this->credential);
			}
		}
		// @codeCoverageIgnoreEnd

		// Set session
		$this->session = $session;
		// Pick appropriate session, if it wasn't provided
		// @codeCoverageIgnoreStart
		if (!$this->session) {
			$this->session = new Session();
			$this->session->isStarted() or $this->session->start();
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
					.AbstractTransport::getApiRoot()
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
				.AbstractTransport::getApiRoot()
				.'/exchange?'.http_build_query($payload);
	}

	public function checkSession($onSuccessCallback = null)
	{
		if (isset($_GET[self::SAAS_API_HASH])) {
			$hash = $_GET[self::SAAS_API_HASH];
			if ($hash == md5($this->credential->getKey())) {
				// Set current session
				$this->session->set(self::SAAS_API_LOGIN, true);
				$this->session->set(self::SAAS_API_USER, $_GET[self::SAAS_API_QS_USER]);
				$this->session->set(self::SAAS_API_COMPANY, $_GET[self::SAAS_API_QS_COMPANY]);

				if (is_callable($onSuccessCallback)) {
					call_user_func($onSuccessCallback);
				} 
			}
		}
	}

	/**
	 * @{inheritDoc}
	 */
	public function isLogin()
	{
		return $this->session->get(self::SAAS_API_LOGIN, false);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getActiveUser()
	{
		return $this->transport->getUser($this->session->get(self::SAAS_API_USER, 0));
	}

	/**
	 * @{inheritDoc}
	 */
	public function getActiveCompany()
	{
		return $this->transport->getCompany($this->session->get(self::SAAS_API_COMPANY, 0));
	}

	/**
	 * @{inheritDoc}
	 */
	public function logout()
	{
		$this->session->remove(self::SAAS_API_LOGIN);
		$this->session->remove(self::SAAS_API_USER);
		$this->session->remove(self::SAAS_API_COMPANY);
	}
}