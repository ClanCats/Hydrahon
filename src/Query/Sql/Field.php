<?php namespace ClanCats\Hydrahon\Query\Sql;

class Field
{
	protected $field = null;

	public function __construct(string $field)
	{
		$this->field = $field;
	}

	public function __toString(): string
	{
		return $this->field;
	}
}

