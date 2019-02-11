<?php
namespace ClanCats\Hydrahon\Query\Sql\Keyword;

use ClanCats\Hydrahon\Query\Sql\Keyword;

class SpecialValue extends Keyword {
	public const KEYWORDS = ['null','default','true','false','unknown'];
}
