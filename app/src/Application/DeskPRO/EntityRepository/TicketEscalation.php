<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Tickets\Actions\SendUserEmail;
use Application\DeskPRO\Tickets\Actions\SetStatus;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketEscalation extends AbstractEntityRepository
{
    static public $definitions = array(
        'satisfaction' => array(
            array(
                'title' => 'Satisfaction request',
                'sys_name' => 'satisfaction',
                'event' => \Application\DeskPRO\Entity\TicketEscalation::EVENT_TYPE_TIME_RESOLVED,
                'default_time' => 259200,// 60 * 60 * 24 * 3
                'default_template' => 'DeskPRO:emails_user:ticket-rate.html.twig',
            ),
        ),
        'statuses' => array(
            1 => array(
                'title' => 'Send warning when awaiting user',
                'sys_name' => 'statuses_awaiting_user_warning',
                'event' => \Application\DeskPRO\Entity\TicketEscalation::EVENT_TYPE_TIME_USER_WAITING,
                'default_time' => 604800,// 60 * 60 * 24 * 7
                'default_template' => 'DeskPRO:emails_user:ticket-awaiting-warn.html.twig',
            ),
            2 => array(
                'title' => 'Send final warning when awaiting user',
                'sys_name' => 'statuses_awaiting_user_final',
                'event' => \Application\DeskPRO\Entity\TicketEscalation::EVENT_TYPE_TIME_USER_WAITING,
                'default_time' => 1209600,// 60 * 60 * 24 * 14
                'default_template' => 'DeskPRO:emails_user:ticket-awaiting-warn.html.twig',
            ),
            3 => array(
                'title' => 'Set status to resolved when awaiting user',
                'sys_name' => 'statuses_awaiting_user_set_resolved',
                'event' => \Application\DeskPRO\Entity\TicketEscalation::EVENT_TYPE_TIME_USER_WAITING,
                'default_time' => 1814400,// 60 * 60 * 24 * 21
                'default_status' => 'resolved',
            ),
            4 => array(
                'title' => 'Set status to archived when ticket has been resolved',
                'sys_name' => 'statuses_resolved_set_archived',
                'event' => \Application\DeskPRO\Entity\TicketEscalation::EVENT_TYPE_TIME_RESOLVED,
                'default_time' => 7776000,// 60 * 60 * 24 * 90
                'default_status' => 'archived',
            ),
            5 => array(
                'title' => 'Set status to archived when ticket has been resolved',
                'sys_name' => 'statuses_resolved_set_archived_2',
                'event' => \Application\DeskPRO\Entity\TicketEscalation::EVENT_TYPE_TIME_RESOLVED,
                'default_time' => 8640000,// 60 * 60 * 24 * 100
                'default_status' => 'archived',
            ),
        ),
    );

    /**
     * @return array
     */
    public function getEscalations()
    {
        return $this->_em->createQuery("
            SELECT te
            FROM DeskPRO:TicketEscalation te
        ")->execute();
    }

    /**
     * @param $special_type
     * @param $id
     * @return \Application\DeskPRO\Entity\TicketEscalation
     * @throws NotFoundHttpException
     */
    public function getSpecialEscalation($special_type, $id)
    {
        if (!$definitions = @self::$definitions[$special_type]) {
            throw new NotFoundHttpException;
        }

        if (!$def = @$definitions[$id]) {
            throw new NotFoundHttpException;
        }

        if ($esc = $this->findOneBy(array('sys_name' => $def['sys_name']))) {
            return $esc;
        }

        $esc = new \Application\DeskPRO\Entity\TicketEscalation();
        $esc['title'] = $def['title'];
        $esc['sys_name'] = $def['sys_name'];
        $esc['event_trigger'] = $def['event'];
        $esc['event_trigger_time'] = $def['default_time'];
        $esc['is_enabled'] = true;

        if (@$def['default_template']) {
            $esc->actions->addAction(new SendUserEmail(array(
                'template' => $def['default_template'],
                'do_cc_users' => false,
                'from_name' => 'helpdesk_name',
                'from_account' => 0,
                'headers' => array(),
            )));
        }

        if (@$def['default_status']) {
            $esc->actions->addAction(new SetStatus(array(
                'status' => $def['default_status'],
            )));
        }

        $this->getEntityManager()->persist($esc);
        $this->getEntityManager()->flush();

        return $esc;
    }
}
