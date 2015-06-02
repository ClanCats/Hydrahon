<?php namespace ClanCats\Hydrahon\Query;

/**
 * DB Expression
 * This class is just an value holder so we are able to identify 
 * if a given string should not be escaped.
 ** 
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

class Expression 
{
	/**
	 * The value holder 
	 *
	 * @var string
	 */
	protected $value = null;
	
	/**
	 * The constructor that assigns our value
	 *
	 * @param string 		$value
	 * @return void
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	/**
	 * Return the expressions value
	 * 
	 * @return string 
	 */
	public function value()
	{
		return $this->value;
	}

	/**
	 * To string magic returns the expression value
	 */
	public function __toString()
	{
		return $this->value();
	}
}