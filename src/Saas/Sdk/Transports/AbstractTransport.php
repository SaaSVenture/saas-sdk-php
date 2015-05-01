<?php namespace Saas\Sdk\Transports;

/**
 * Abstract Transport
 *
 * @author Taufan Adhitya <toopay@taufanaditya.com>
 * @package saas/sdk
 */

use Saas\Sdk\Contracts\TransportInterface;

abstract class AbstractTransport
{
	/**
	 * Main API root resolver
	 *
	 * @return string
	 */
	public static function getApiRoot()
	{
		$root = !defined('SAAS_API_DEFAULT_ROOT') 
			? TransportInterface::SAAS_API_DEFAULT_ROOT
			: SAAS_API_DEFAULT_ROOT;

		return rtrim($root,'/');
	}

	/**
	 * Main API dev root resolver
	 *
	 * @return string
	 */
	public static function getApiDevRoot()
	{
		$root = !defined('SAAS_API_DEVELOPER_ROOT') 
			? TransportInterface::SAAS_API_DEVELOPER_ROOT
			: SAAS_API_DEVELOPER_ROOT;

		return rtrim($root,'/');
	}
}