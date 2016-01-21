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

class BuildNewAgent_0590 extends AbstractBuild
{
    public function run()
    {
        $this->out('add ticket reminders job');

        $j                 = new \Application\DeskPRO\Entity\WorkerJob();
        $j['id']           = 'ticket_reminders';
        $j['worker_group'] = 'ticket_reminders';
        $j['title']        = 'Ticket Reminders';
        $j['description']  = 'Sends reminders to users who created a ticket but have not yet validated their email';
        $j['job_class']    = 'Application\\DeskPRO\\WorkerProcess\\Job\\TicketReminders';
        $j['interval']     = \Application\DeskPRO\WorkerProcess\Job\TicketReminders::DEFAULT_INTERVAL;
        $this->container->getEm()->persist($j);
        $this->container->getEm()->flush();
    }
}

//[[build:1456790459]]

