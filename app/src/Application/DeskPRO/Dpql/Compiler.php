<?php

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
			$this->_parser->doParse($this->_lexer->token, $this->_lexer->value);
		}
		$this->_parser->doParse(0, 0);

		return $this->_parser->getResult();
	}
}