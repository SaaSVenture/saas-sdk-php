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
	const SAAS_API_QS_USER = 'user_id';
	const SAAS_API_QS_COMPANY = 'company_id';
	const SAAS_API_LOGIN = 'saas_api_login';
	const SAAS_API_USER = 'saas_api_user';
	const SAAS_API_COMPANY = 'saas_api_company';

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
	 * @return string
	 */
	public function getExchangeUrl($userId = null, $companyId = null);

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
	 * Destroy current active session
	 *
	 * @return void
	 */
	public function logout();
}