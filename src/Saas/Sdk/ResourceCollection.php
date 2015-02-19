<?php namespace Saas\Sdk;

/**
 * Resource Collection
 *
 * @author Taufan Adhitya <toopay@taufanaditya.com>
 * @package saas/sdk
 */

use Saas\Sdk\ResourceObject;
use IteratorAggregate, ArrayIterator;

class ResourceCollection implements IteratorAggregate
{
	/**
	 * @var array
	 */
	private $collection = array();

	/**
	 * Constructor
	 *
	 * @param array
	 */
	public function __construct(array $collection)
	{
		foreach ($collection as $resource)  {
			$this->collection[] = new ResourceObject($resource);
		}
	}

	/**
	 * @{inheritDoc}
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->collection);
	}
}