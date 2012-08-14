<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Dpql
 */

namespace Application\DeskPRO\Dpql;

class Compiler
{
	protected $_lexer;
	protected $_parser;

	public function __construct(Lexer $lexer = null, Parser $parser = null)
	{
		if (!$lexer) $lexer = new Lexer();
		if (!$parser) $parser = new Parser();

		$this->_lexer = $lexer;
		$this->_parser = $parser;
	}

	public function compile($input)
	{
		$this->_lexer->setInput($input);

		while ($this->_lexer->yylex()) {
			$this->_parser->line = $this->_lexer->line;
			$this->_parser->doParse($this->_lexer->token, $this->_lexer->value);
		}
		$this->_parser->doParse(0, 0);

		$statement = $this->_parser->getResult();
		$statement->prepare();

		return $statement;
	}
}