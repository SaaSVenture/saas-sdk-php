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
	 * Main API root resolved
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
}