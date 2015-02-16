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

class LocalTransport implements TransportInterface
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