<?php namespace Saas\Sdk\Transports;

/**
 * Remote transport layer
 *
 * @author Taufan Adhitya <toopay@taufanaditya.com>
 * @package saas/sdk
 */

use Saas\Sdk\Contracts\TransportInterface;
use Saas\Sdk\Credential;
use Saas\Sdk\ResourceObject;
use Saas\Sdk\ResourceCollection;
use Guzzle\Http\Client;
use Exception;

class RemoteTransport extends AbstractTransport implements TransportInterface
{
	/**
	 * @var Saas\Sdk\Credential
	 */
	private $credential;

	/**
	 * @var GuzzleHttp\Client
	 */
	private $client;

	/**
	 * @var array
	 */
	private $defaultHeaders = array();

	/**
	 * Constructor
	 *
	 * @param Saas\Sdk\Credential
	 */
	public function __construct(Credential $credential)
	{
		$this->credential = $credential;
		$this->defaultHeaders = array(
			'X-Saas-Origin-Domain' => $_SERVER['HTTP_HOST'],
			'Authorization' => 'Basic '.$this->getAuthorizationHash(),
		);

		$this->client = new Client($this->baseUrl());
	}

	/**
	 * @{inheritDoc}
	 */
	public function getOwnerApp()
	{
		try {
			$response = $this->client->get('/api/instance', $this->defaultHeaders)->send();
			$brandData = $response->getBody();
			return new ResourceObject(json_decode($brandData,true));
		} catch (Exception $e) {
			return new ResourceObject();
		}
	}

	/**
	 * @{inheritDoc}
	 */
	public function getOwnerAppIdentity()
	{
		try {
			$response = $this->client->get('/api/instance/identity', $this->defaultHeaders)->send();
			$identityData = $response->getBody();
			return new ResourceObject(json_decode($identityData,true));
		} catch (Exception $e) {
			return new ResourceObject();
		}
	}

	/**
	 * @{inheritDoc}
	 */
	public function getUser($id)
	{
		try {
			$response = $this->client->get('/api/user/'.$id, $this->defaultHeaders)->send();
			$userData = $response->getBody();
			return new ResourceObject(json_decode($userData,true));
		} catch (Exception $e) {
			return new ResourceObject();
		}
	}

	/**
	 * @{inheritDoc}
	 */
	public function getCompany($id)
	{
		try {
			$response = $this->client->get('/api/company/'.$id, $this->defaultHeaders)->send();
			$brandData = $response->getBody();
			return new ResourceObject(json_decode($brandData,true));
		} catch (Exception $e) {
			return new ResourceObject();
		}
	}

	/**
	 * @{inheritDoc}
	 */
	public function switchCompany($userId, $brandId)
	{
		try {
			$response = $this->client->post('/api/switch', $this->defaultHeaders, array('user_id' => $userId, 'brand_id' => $brandId))->send();
			$brandData = $response->getBody();
			return new ResourceObject(json_decode($brandData,true));
		} catch (Exception $e) {
			return new ResourceObject();
		}
	}

	/**
	 * @{inheritDoc}
	 */
	public function getCompaniesByUser($userId = 0, $onlyActive = false)
	{
		try {
			$response = $this->client->get('/api/company?'.http_build_query(array(
				'user_id' => $userId,
				'only_active' => var_export($onlyActive, true),
			)), $this->defaultHeaders)->send();
			$companiesData = $response->getBody();
			return new ResourceCollection(json_decode($companiesData,true));
		} catch (Exception $e) {
			return new ResourceCollection();
		}
	}

	/**
	 * @{inheritDoc}
	 */
	public function getCurrentSubscription($companyId)
	{
		try {
			$response = $this->client->get('/api/company/'.$companyId.'/subscription', $this->defaultHeaders)->send();
			$subscriptionData = $response->getBody();
			return new ResourceObject(json_decode($subscriptionData,true));
		} catch (Exception $e) {
			return new ResourceObject();
		}
	}

	/**
	 * @{inheritDoc}
	 */
	public function clearSession($sessionId)
	{
		try {
			$this->client->get('/api/clearsession/'.$sessionId, $this->defaultHeaders)->send();
		} catch (Exception $e) {
			// Supress any error
		}
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPlans()
	{
		try {
			$response = $this->client->get('/api/plans', $this->defaultHeaders)->send();
			$plansData = $response->getBody();
			return new ResourceCollection(json_decode($plansData,true));
		} catch (Exception $e) {
			return new ResourceCollection();
		}
	}

	/**
	 * @{inheritDoc}
	 */
	public function getRules()
	{
		try {
			$response = $this->client->get('/api/rules', $this->defaultHeaders)->send();
			$rulesData = $response->getBody();
			return new ResourceCollection(json_decode($rulesData,true));
		} catch (Exception $e) {
			return new ResourceCollection();
		}
	}

	/**
	 * @{inheritDoc}
	 */
	public function getRule($slug = null)
	{
		try {
			$response = $this->client->get('/api/rules/'.$slug, $this->defaultHeaders)->send();
			$ruleData = $response->getBody();
			return new ResourceObject(json_decode($ruleData,true));
		} catch (Exception $e) {
			return new ResourceObject();
		}
	}

	/**
	 * @{inheritDoc}
	 */
	public function checkAcl($rule = null, 
							ResourceObject $user = null,
							ResourceObject $company = null,
							ResourceObject $subscription = null)
	{
		try {
			$response = $this->client->get('/api/acl?'.http_build_query(array(
				'rule' => $rule,
				'user_id' => $user->id,
				'company_id' => $company->id,
				'subscription_id' => $subscription->id,
			)), $this->defaultHeaders)->send();
			$aclData = $response->getBody();
			$aclResult = json_decode($aclData);
			return (bool) $aclResult->is_allowed;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Get the base Saas API url
	 *
	 * @param mixed
	 * @return string
	 */
	protected function baseUrl($path = '')
	{
		return 'http://'.static::getApiRoot().$path;
	}

	/**
	 * Get Authorization hash
	 *
	 * @param string
	 */
	protected function getAuthorizationHash()
	{
		return base64_encode($this->credential->getKey().':'.$this->credential->getSecret());
	}
}