<?php namespace Saas\Sdk;

/**
 * Resource Object Representation (POPO - Plain PHP Object)
 *
 * @author Taufan Adhitya <toopay@taufanaditya.com>
 * @package saas/sdk
 */

class ResourceObject
{
	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * Constructor
	 *
	 * @param mixed 
	 */
	public function __construct($resource)
	{
		if (!empty($resource)) {
			$this->data = (array) $resource;
		}
	}

	/**
	 * Global getter overider
	 *
	 * @param string
	 * @return mixed
	 */
	public function __get($name)
	{
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}

	/**
	 * Global setter overider
	 *
	 * @param string
	 * @param mixed
	 */
	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}
}