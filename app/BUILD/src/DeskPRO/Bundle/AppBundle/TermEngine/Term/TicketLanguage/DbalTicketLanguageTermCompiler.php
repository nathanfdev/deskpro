<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLanguage;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketLanguageTermCompiler.
 */
class DbalTicketLanguageTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $value = $term->getOption('language');

        $qp = new DbalQueryPart();
        $qp
            ->addUniqueJoin(
                'languages',
                'languages',
                '{languages}.id = ticket.language_id'
            )
            ->setParameter('language', $value)
        ;

        if ($this->isOp($term->getOp(), TermInterface::OP_NOT)) {
            if ($value) {
                $qp->setWhereString('({languages}.id NOT IN(:language) AND {languages}.lang_code NOT IN(:language)) OR {languages}.id IS NULL');
            } else {
                $qp->setWhereString('{languages}.id IS NOT NULL');
            }
        } else {
            if ($value) {
                $qp->setWhereString('{languages}.id IN(:language) OR {languages}.lang_code IN(:language)');
            } else {
                $qp->setWhereString('{languages}.id IS NULL');
            }
        }

        $this->logQueryPart($qp);

        return $qp;
    }
}
