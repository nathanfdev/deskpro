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

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Entity\Ticket;

class BuildNewAgent_0520 extends AbstractBuild
{
    public function run()
    {
        $this->out('Clean up old temp and validating tickets');
        $this->execMutateSql(
            'UPDATE tickets SET hidden_status = ?, date_status = ? WHERE status = ? AND hidden_status IN (?, ?)',
            array(
                /*Ticket::HIDDEN_STATUS_DELETED*/'deleted',
                date('Y-m-d H:i:s'),
                /*Ticket::STATUS_HIDDEN*/'hidden',
                /*Ticket::HIDDEN_STATUS_TEMP*/'temp',
                /*Ticket::HIDDEN_STATUS_VALIDATING*/'validating',
            )
        );
    }
}

//[[build:1456790452]]

