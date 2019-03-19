<?php
namespace ClanCats\Hydrahon\Query\Sql\Keyword;

use ClanCats\Hydrahon\Query\Sql\Keyword;

class BinOp extends Keyword {
	public const KEYWORDS = ['=','>=','>','<=','<','<>','!=','in','not in','is','is not','like','not like','regexp','not regexp','<=>','between','not between'];
}
