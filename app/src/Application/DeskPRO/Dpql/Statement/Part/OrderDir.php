<?php

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Parser;
use Application\DeskPRO\Dpql\Exception;

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
		throw new Exception('Order direction prepare() cannot not be called');
	}

	public function toDpql(Display $statement, $section, array $stack)
	{
		return $this->order->toDpql($statement, $section, $stack) . ' ' . $this->orderDir;
	}
}