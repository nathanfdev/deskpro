<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Parser;

class OrderDir extends AbstractPart
{
	public $order;
	public $orderDir;

	public function __construct(AbstractPart $order, $orderDir)
	{
		$this->order = $order;
		$this->orderDir = $orderDir;
	}

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		throw new \Exception('Order direction prepare should not be called');
	}
}