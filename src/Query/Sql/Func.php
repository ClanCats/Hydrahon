<?php namespace ClanCats\Hydrahon\Query\Sql;

/**
 * DB Function
 ** 
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
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
	 * @param array
	 */
	protected $arguments = array();
	
	/**
	 * The constructor that assigns our value
	 *
	 * @param string 		$name
	 * @param ...
	 * @return void
	 */
	public function __construct()
	{
		$arguments = func_get_args();

		// throw an error when no arguments are given
		if (empty($arguments))
		{
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
	public function name()
	{
		return $this->name;
	}

	/**
	 * Return the functions arguments
	 * 
	 * @return string 
	 */
	public function arguments()
	{
		return $this->arguments;
	}
}