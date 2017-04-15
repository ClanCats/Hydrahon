<?php namespace ClanCats\Hydrahon\Test;
/**
 * Hydrahon builder test 
 ** 
 *
 * @package 		Hydrahon
 * @copyright 		Mario Döring
 */

use ClanCats\Hydrahon\BaseQuery;

abstract class Query_QueryCase extends \PHPUnit\Framework\TestCase
{
	protected $queryClass;

	/**
	 * Returns an new query object
	 * 
	 * @param mixed 		$results
	 * 
	 * @return ClanCats\Hydrahon\Query\Sql\BaseQuery
	 */
	protected function createQuery($result = null)
	{
		$query = new $this->queryClass;
		$query->setResultFetcher(function() use($result)
		{
			return $result;
		});

		return $query;
	}

	/**
	 * Returns all attributes or a specific one
	 * 
	 * @param BaseQuery 						$query
	 * @param string 							$key
	 * @return mixed
	 */
	protected function attributes(BaseQuery $query, $key = null)
	{
		$attributes = array_filter($query->attributes());

		foreach($attributes as $queryKey => &$value)
		{
			if ($queryKey === 'wheres' && is_array($value))
			{
				foreach($value as &$where)
				{
					if (isset($where[1]) && $where[1] instanceof BaseQuery)
					{
						$where[1] = $this->attributes($where[1]);
					}
				}
			}

			if ($queryKey === 'joins' && is_array($value))
			{
				foreach($value as &$join)
				{
					if (isset($join[2]) && $join[2] instanceof BaseQuery)
					{
						$join[2] = $this->attributes($join[2]);
					}
				}
			}
		}

		if (!is_null($key))
		{
			if (!isset($attributes[$key]))
			{
				return null;
			}

			return $attributes[$key];
		}

		return $attributes;
	}

	/**
	 * Asserts the attributes of the given query
	 * 
	 * @param ClanCats\Hydrahon\Query\Sql\BaseQuery 		$query
	 * @param array 										$attributes
	 * @return void
	 */
	protected function assertAttributes(BaseQuery $query, array $attributes = array())
	{
		$this->assertEquals($attributes, $this->attributes($query));
	}
}