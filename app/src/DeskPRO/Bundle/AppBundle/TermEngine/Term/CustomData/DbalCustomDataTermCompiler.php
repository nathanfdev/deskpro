<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\CustomData;

use Application\DeskPRO\Entity\CustomDataAbstract;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalCustomDataTermCompiler.
 */
class DbalCustomDataTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $query_part = new DbalQueryPart();
        $query_part->addUniqueJoin(
            'custom_data_ticket',
            'custom_data_ticket',
            '{custom_data_ticket}.ticket_id = ticket.id AND
             {custom_data_ticket}.field_id = '.intval($term->getOption('field_id'))
        );
        $query_part->addUniqueJoin(
            'custom_def_ticket',
            'custom_def_ticket',
            '{custom_data_ticket}.field_id = {custom_def_ticket}.id'
        );
        $query_part->setParameter('custom_data_value', $term->getOption('custom_data_value'));

        $dataSql = CustomDataAbstract::getDataSql('{custom_data_ticket}', '{custom_def_ticket}');
        $query_part->setWhereString("$dataSql = :custom_data_value");

        $this->logQueryPart($query_part);

        return $query_part;
    }
}
