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
}