<?php

namespace DeskPRO\Bundle\PortalBundle\DoctrineExtension;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;

class StrictGreatest extends FunctionNode
{
    private $field = null;

    private $values = [];

    /**
     * @param Parser $parser
     * @throws QueryException
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->field = $parser->ArithmeticExpression();
        $lexer = $parser->getLexer();

        while (count($this->values) < 1 ||
               $lexer->lookahead['type'] !== Lexer::T_CLOSE_PARENTHESIS) {
            $parser->match(Lexer::T_COMMA);
            $this->values[] = $parser->ArithmeticExpression();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        $query = 'GREATEST(';

        $query .= 'COALESCE('.$this->field->dispatch($sqlWalker).', 0)';

        $query .= ', ';

        foreach ($this->values as $i => $iValue) {
            if ($i > 0) {
                $query .= ', ';
            }


            $query .= 'COALESCE(' . $iValue->dispatch($sqlWalker) . ', 0)';
        }

        $query .= ')';

        return $query;
    }
}
