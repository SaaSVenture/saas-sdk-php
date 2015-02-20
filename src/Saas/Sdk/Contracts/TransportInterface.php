<?php namespace Saas\Sdk\Contracts;

/**
 * Transport Layer Interface
 *
 * @author Taufan Adhitya <toopay@taufanaditya.com>
 * @package saas/sdk
 */

interface TransportInterface 
{
	const SAAS_API_APP = 'app';
	const SAAS_API_BOOTSTRAP = 'bootstrap';
	const SAAS_API_INSTANCE = 'instances';
	const SAAS_API_ROOT_DIR = 'app-saasapi';
	const SAAS_API_DEFAULT_ROOT = 'saasapi.com';
	const SAAS_API_EXT = '.php';
	const SAAS_API_DOMAIN_SEPARATOR = '.';

	/**
	 * Get the app's that own this API subscription
	 *
	 * @return Saas\Sdk\ResourceObject
	 */
	public function getOwnerApp();

	/**
	 * Get user resource
	 *
	 * @return Saas\Sdk\ResourceObject
	 */
	public function getUser($id);

	/**
	 * Get company resource
	 *
	 * @return Saas\Sdk\ResourceObject
	 */
	public function getCompany($id);

	/**
	 * Get current company subscription
	 *
	 * @return Saas\Sdk\ResourceObject
	 */
	public function getCurrentSubscription($companyId);

	/**
	 * Clear specific session id
	 *
	 * @param string Session ID
	 * @return void
	 */
	public function clearSession($sessionId);

	/**
	 * Get available plans
	 *
	 * @return Saas\Sdk\ResourceCollection
	 */
	public function getPlans();
}