<?php

namespace DeskPRO\Bundle\AppBundle\DBAL\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Subselect;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Class FirstFunction
 * @package DeskPRO\Bundle\AppBundle\DBAL\Functions
 */
class FirstFunction extends FunctionNode
{
    /**
     * @var Subselect
     */
    private $subSelect;

    /**
     * {@inheritdoc}
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->subSelect = $parser->Subselect();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return '(' . $this->subSelect->dispatch($sqlWalker) . ' LIMIT 1)';
    }
}
