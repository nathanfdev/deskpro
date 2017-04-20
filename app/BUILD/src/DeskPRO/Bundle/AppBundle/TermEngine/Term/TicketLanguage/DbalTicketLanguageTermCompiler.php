<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
        $query_part = new DbalQueryPart();

        $query_part->addUniqueJoin(
            'languages',
            'languages',
            '{languages}.id = ticket.language_id'
        );

        $language = $term->getOption('language');
        $query_part->setParameter('language', $language);
        $op = $term->getOp();

        $whereString = $this->isOp($op, TermInterface::OP_NOT)
            ? $this->getNotEqualWhereString($language)
            : $this->getEqualWhereString($language);

        $query_part->setWhereString($whereString);

        $this->logQueryPart($query_part);

        return $query_part;
    }

    /**
     * @param $language
     *
     * @return string
     */
    private function getNotEqualWhereString($language)
    {
        if (!$language) {
            return '{languages}.id IS NOT NULL';
        }

        return '({languages}.id != :language AND {languages}.lang_code != :language) OR {languages}.id IS NULL';
    }

    /**
     * @param $language
     *
     * @return string
     */
    private function getEqualWhereString($language)
    {
        if (!$language) {
            return '{languages}.id IS NULL';
        }

        return '{languages}.id = :language OR {languages}.lang_code = :language';
    }
}
