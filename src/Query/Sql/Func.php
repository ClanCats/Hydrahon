<?php 

namespace ClanCats\Hydrahon\Query\Sql;

/**
 * DB Function
 ** 
 * @link      https://github.com/ClanCats/Hydrahon/
 * @copyright Copyright (c) 2015-2019 Mario Döring
 * @license   https://github.com/ClanCats/Hydrahon/blob/master/LICENSE (MIT License)
 */

class Func 
{
	/**
	 * the function name
	 *
	 * @var string
	 */
	protected $name = null;

	/**
	 * The function arguments
	 *
	 * @var array<mixed>
	 */
	protected $arguments = [];
	
	/**
	 * The constructor that assigns our value
	 *
	 * @param string 		$name
	 * @param array...		$arguments
	 * @return void
	 */
	public function __construct(...$arguments)
	{
		// throw an error when no arguments are given
		if (empty($arguments)) {
			throw new Exception("Cannot create function expression without arguments.");
		}

		// the first argument is always the function name
		$this->name = array_shift($arguments);

		// and assign the arguments
		$this->arguments = $arguments;
	}

	/**
	 * Return the functions name
	 * 
	 * @return string 
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Return the functions arguments
	 * 
	 * @return array 
	 */
	public function arguments(): array
	{
		return $this->arguments;
	}
}
