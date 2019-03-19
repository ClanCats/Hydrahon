<?php namespace ClanCats\Hydrahon\Query\Sql;

class Keyword
{
	protected $keyword = null;
	public const KEYWORDS = ['select','from','where','update','table','set','insert','delete','truncate','join'];

	public function __construct(string $keyword)
	{
		if (empty($this::KEYWORDS))
		{
			throw new Exception('Invalid keyword type');
		}

		$keyword = trim(strtolower($keyword));

		if (!in_array($keyword,$this::KEYWORDS))
		{
			throw new Exception('Invalid keyword given "'.$keyword.'". Available types are '.implode(', ',$this::KEYWORDS));
		}

		$this->keyword = $keyword;
	}

	public function __toString(): string
	{
		return $this->keyword;
	}
}

