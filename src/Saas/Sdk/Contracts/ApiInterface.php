<?php namespace Saas\Sdk\Contracts;

/**
 * API Interface
 *
 * @author Taufan Adhitya <toopay@taufanaditya.com>
 * @package saas/sdk
 */

interface ApiInterface
{
	const SAAS_API_HTTP_SCHEME = 'http://';
	const SAAS_API_HASH = 'saas_hash';
	const SAAS_API_QS_SESSION = 'session_id';
	const SAAS_API_QS_USER = 'user_id';
	const SAAS_API_QS_COMPANY = 'company_id';
	const SAAS_API_LOGIN = 'saas_api_login';
	const SAAS_API_SESSION = 'saas_api_session';
	const SAAS_API_USER = 'saas_api_user';
	const SAAS_API_COMPANY = 'saas_api_company';
	const SAAS_API_ACTIVE_USER = 'active_user';
	const SAAS_API_ACTIVE_COMPANY = 'active_company';
	const SAAS_API_ACTIVE_SUBSCRIPTION = 'active_subscription';

	/**
	 * Get the original app url
	 *
	 * @return string
	 */
	public function getOriginalAppUrl();

	/**
	 * Get the authorization url
	 *
	 * @return string
	 */
	public function getLoginUrl();

	/**
	 * Get the exchange url
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return string
	 */
	public function getExchangeUrl($userId = null, $companyId = null, $sessionId = null, $interactiveMode = false);

	/**
	 * Get the profile url for current active user
	 *
	 * @return string 
	 */
	public function getProfileUrl();

	/**
	 * Get the wallet url for current active user
	 *
	 * @return string
	 */
	public function getWalletUrl();

	/**
	 * Get the subscription url for current active brand
	 *
	 * @return string
	 */
	public function getSubscriptionUrl();

	/**
	 * Get purchase url
	 *
	 * @param string Plan title
	 * @return string url
	 */
	public function getPurchaseUrl($plan);

	/**
	 * Get instance branding data (Identity)
	 *
	 * @return Saas\Sdk\ResourceObject
	 */
	public function getOriginalAppIdentity();

	/**
	 * Check session
	 *
	 * @return void
	 */
	public function checkSession($onSuccessCallback = null);

	/**
	 * Check whether current user are logged in
	 *
	 * @return bool
	 */
	public function isLogin();

	/**
	 * Get current active user
	 *
	 * @return Saas\Sdk\ResourceObject
	 */
	public function getActiveUser();

	/**
	 * Get current active company
	 *
	 * @return Saas\Sdk\ResourceObject
	 */
	public function getActiveCompany();

	/**
	 * Set active company
	 *
	 * @param int Intended brand id
	 * @return Saas\Sdk\ResourceObject
	 */
	public function setActiveCompany($id);

	/**
	 * Get current active user's companies
	 *
	 * @return Saas\Sdk\ResourceCollection
	 */
	public function getActiveUserCompanies();

	/**
	 * Get current active subscription
	 *
	 * @return Saas\Sdk\ResourceObject
	 */
	public function getActiveSubscription();

	/**
	 * Get user by id
	 *
	 * @param int
	 * @return Saas\Sdk\ResourceObject
	 */
	public function getUser($id = 0);

	/**
	 * Get company by id
	 *
	 * @param int
	 * @return Saas\Sdk\ResourceObject
	 */
	public function getCompany($id = 0);

	/**
	 * Get companies by user
	 */
	public function getUserCompanies($userId = 0, $onlyActive = false);

	/**
	 * Destroy current active session
	 *
	 * @return void
	 */
	public function logout();

	/**
	 * Get available plans
	 *
	 * @return Saas\Sdk\ResourceCollection
	 */
	public function getPlans();

	/**
	 * Get available rules
	 *
	 * @return Saas\Sdk\ResourceCollection
	 */
	public function getRules();

	/**
	 * Get rules by nickname/slug
	 *
	 * @return Saas\Sdk\ResourceObject
	 */
	public function getRule($slug = null);

	/**
	 * Main ACL assertion API
	 *
	 * @return bool
	 */
	public function isAllowed($rule = null);
}