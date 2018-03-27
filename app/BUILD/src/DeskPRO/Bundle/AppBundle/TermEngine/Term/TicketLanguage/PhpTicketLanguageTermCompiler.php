<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLanguage;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketLanguageTermCompiler.
 */
class PhpTicketLanguageTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        $op        = $term->getOp();
        $composite = $this->isOp($term->getOp(), TermInterface::OP_NOT) ? '&&' : '||';

        return new PhpCheck(
            "check_contains(ticket.getLanguage().lang_code, :op, :language) $composite check_contains(ticket.getLanguage().id, :op, :language)",
            ['op' => $op, 'language' => $term->getOption('language')]
        );
    }
}
