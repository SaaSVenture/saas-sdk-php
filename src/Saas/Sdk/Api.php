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
		return $this->getAppUrl('/auth/login');
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProfileUrl()
	{
		return $this->getAppUrl('/user/profile/edit');
	}

	/**
	 * @{inheritDoc}
	 */
	public function getWalletUrl()
	{
		return $this->getAppUrl('/user/wallet');
	}

	/**
	 * @{inheritDoc}
	 */
	public function getSubscriptionUrl()
	{
		return $this->getAppUrl('/brand/subscription');
	}

	/**
	 * @{inheritDoc}
	 */
	public function getExchangeUrl($userId = null, $companyId = null, $sessionId = null)
	{
		// Main payload, API key and secret
		$payload = array('key' => $this->credential->getKey(), 'secret' => $this->credential->getSecret());

		// User id and Company id (active)
		if (!empty($userId) && !empty($companyId)) {
			$payload['user_id'] = $userId;
			$payload['company_id'] = $companyId;
		}

		// Session id
		if (!empty($sessionId)) $payload['session_id'] = $sessionId;

		return self::SAAS_API_HTTP_SCHEME
				.AbstractTransport::getApiRoot()
				.'/exchange?'.http_build_query($payload);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPurchaseUrl($plan)
	{
		return $this->getAppUrl('/start/'.$plan);
	}

	public function checkSession($onSuccessCallback = null)
	{
		if (isset($_GET[self::SAAS_API_HASH])) {
			$hash = $_GET[self::SAAS_API_HASH];
			if ($hash == md5($this->credential->getKey())) {
				// Set current session
				$this->session->set(self::SAAS_API_LOGIN, true);
				$this->session->set(self::SAAS_API_SESSION, $_GET[self::SAAS_API_QS_SESSION]);
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
		return $this->getUser($this->session->get(self::SAAS_API_USER, 0));
	}

	/**
	 * @{inheritDoc}
	 */
	public function getActiveCompany()
	{
		$companies = $this->getUserCompanies($this->session->get(self::SAAS_API_COMPANY, 0), true);
		return $companies->getIterator()->current();
	}

	/**
	 * @{inheritDoc}
	 */
	public function getActiveUserCompanies()
	{
		return $this->getUserCompanies($this->session->get(self::SAAS_API_USER, 0));
	}

	/**
	 * @{inheritDoc}
	 */
	public function getActiveSubscription()
	{
		return $this->transport->getCurrentSubscription($this->session->get(self::SAAS_API_COMPANY, 0));
	}

	/**
	 * @{inheritDoc}
	 */
	public function getUser($id = 0)
	{
		return $this->transport->getUser($id);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getCompany($id = 0)
	{
		return $this->transport->getCompany($id);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getUserCompanies($userId = 0, $onlyActive = false)
	{
		return $this->transport->getCompaniesByUser($userId);
	}

	/**
	 * @{inheritDoc}
	 */
	public function logout()
	{
		$sessionId = $this->session->get(self::SAAS_API_SESSION);
		$this->session->remove(self::SAAS_API_LOGIN);
		$this->session->remove(self::SAAS_API_SESSION);
		$this->session->remove(self::SAAS_API_USER);
		$this->session->remove(self::SAAS_API_COMPANY);

		$this->transport->clearSession($sessionId);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPlans()
	{
		return $this->transport->getPlans();
	}

	/**
	 * Common App URL Generator
	 *
	 * @param string Path
	 * @return string Full URI
	 */
	protected function getAppUrl($path)
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
					.$path;
	}
}