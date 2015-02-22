<?php namespace Saas\Sdk\Transports;

/**
 * Local transport layer
 *
 * @author Taufan Adhitya <toopay@taufanaditya.com>
 * @package saas/sdk
 */

use Saas\Sdk\Contracts\TransportInterface;
use Saas\Sdk\Credential;
use Saas\Sdk\ResourceObject;
use RuntimeException;

class LocalTransport extends AbstractTransport implements TransportInterface
{
	/**
	 * @var Saas\Sdk\Credential
	 */
	private $credential;

	/**
	 * Constructor
	 *
	 * @param Saas\Sdk\Credential
	 */
	public function __construct(Credential $credential)
	{
		$this->credential = $credential;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getOwnerApp()
	{
		$brand = $this->getApiDBGateway()
					->table('brands')
					->where('key', $this->credential->getKey())
					->where('secret', $this->credential->getSecret())
					->first();

		return new ResourceObject($brand);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getUser($id)
	{
		$user = $this->getApiDBGateway()
					->table('users')
					->where('id', $id)
					->first();

		return new ResourceObject($user);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getCompany($id)
	{
		$company = $this->getApiDBGateway()
						->table('brands')
						->where('id', $id)
						->first();

		return new ResourceObject($company);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getCompaniesByUser($userId = 0, $onlyActive = false)
	{
		$query = $this->getApiDBGateway()
						->table('brands')
						->join('group_user_brand', 'brands.id', '=', 'group_user_brand.brand_id')
						->where('group_user_brand.user_id', $userId)
						->whereNull('group_user_brand.deleted_at');

		if ($onlyActive) $query = $query->where('group_user_brand.active', 1);
		
		$companies = $query->get();

		return new ResourceCollection($companies);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getCurrentSubscription($companyId)
	{
		$subscription = $this->getApiDBGateway()
							->table('subscriptions')
							->where('brand_id', $companyId)
							->whereIn('status', array('active','suspended','pending','expired'))
							->orderBy('status', 'asc')
							->first();

		return new ResourceObject($subscription);
	}

	/**
	 * @{inheritDoc}
	 */
	public function clearSession($sessionId)
	{
		throw new Exception('Not implemented');
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPlans()
	{
		throw new Exception('Not implemented');
	}

	/**
	 * Get DB Gateway
	 *
	 * @return Laravel DB
	 */
	public function getApiDBGateway()
	{
		$instanceRootDir = app_path();
		$host = $_SERVER['HTTP_HOST'];

		$masterAppDb = str_replace(self::SAAS_API_INSTANCE.DIRECTORY_SEPARATOR.$host.DIRECTORY_SEPARATOR.self::SAAS_API_APP,
									 self::SAAS_API_ROOT_DIR.DIRECTORY_SEPARATOR.self::SAAS_API_BOOTSTRAP.DIRECTORY_SEPARATOR.self::SAAS_API_APP.self::SAAS_API_EXT, 
									 $instanceRootDir);

		if (is_file($masterAppDb)) {
			$db = require $masterAppDb;
			return $db;
		} else {
			throw new RuntimeException('Invalid instance path!');
		}
	}
}