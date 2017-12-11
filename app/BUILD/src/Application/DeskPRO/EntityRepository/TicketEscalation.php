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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TicketEscalation as TicketEscalationEntity;
use Application\DeskPRO\Tickets\Actions\SendUserEmail;
use Application\DeskPRO\Tickets\Actions\SendUserNewEmail;
use Application\DeskPRO\Tickets\Actions\SetStatus;
use Application\DeskPRO\Tickets\Filters\FilterTerms;
use Application\DeskPRO\Tickets\Filters\LegacyTermsTransformer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketEscalation extends AbstractEntityRepository
{
    public static $definitions = [
        'satisfaction' => [
            [
                'title'                => 'Satisfaction request',
                'sys_name'             => 'satisfaction',
                'event'                => TicketEscalationEntity::EVENT_TYPE_TIME_RESOLVED,
                'default_time'         => 259200, // 60 * 60 * 24 * 3
                'default_template'     => 'DeskPRO:emails_user:ticket-rate.html.twig',
                'default_new_template' => 'SendmailBundle:emails_user:ticket_rate.html.twig',
                'terms'                => [
                    [
                        'type'    => 'FilterDateLastAgentReply',
                        'op'      => 'gte',
                        'options' => [
                            'date2' => 1,
                            'value' => 'date',
                        ],
                    ],
                    [
                        'type'    => 'FilterFeedbackRating',
                        'op'      => 'not',
                        'options' => ['rating' => 'set'],
                    ],
                ],
            ],
        ],
        'statuses' => [
            1 => [
                'title'                => 'Send warning when awaiting user',
                'sys_name'             => 'statuses_awaiting_user_warning',
                'event'                => TicketEscalationEntity::EVENT_TYPE_TIME_AGENT_WAITING,
                'default_time'         => 604800, // 60 * 60 * 24 * 7
                'default_template'     => 'DeskPRO:emails_user:ticket-awaiting-warn.html.twig',
                'default_new_template' => 'SendmailBundle:emails_user:ticket_awaiting_warn.html.twig',
            ],
            2 => [
                'title'                => 'Send final warning when awaiting user',
                'sys_name'             => 'statuses_awaiting_user_final',
                'event'                => TicketEscalationEntity::EVENT_TYPE_TIME_AGENT_WAITING,
                'default_time'         => 1209600, // 60 * 60 * 24 * 14
                'default_template'     => 'DeskPRO:emails_user:ticket-awaiting-warn-final.html.twig',
                'default_new_template' => 'SendmailBundle:emails_user:ticket_awaiting_warn_final.html.twig',
            ],
            3 => [
                'title'          => 'Set status to resolved when awaiting user',
                'sys_name'       => 'statuses_awaiting_user_set_resolved',
                'event'          => TicketEscalationEntity::EVENT_TYPE_TIME_AGENT_WAITING,
                'default_time'   => 1814400, // 60 * 60 * 24 * 21
                'default_status' => 'resolved',
            ],
        ],
    ];

    /**
     * @return array
     */
    public function getEscalations()
    {
        return $this->_em->createQuery('
            SELECT te
            FROM DeskPRO:TicketEscalation te
        ')->execute();
    }

    /**
     * @param $special_type
     * @param $id
     *
     * @throws NotFoundHttpException
     *
     * @return TicketEscalationEntity
     */
    public function getSpecialEscalation($special_type, $id)
    {
        if (!$definitions = @self::$definitions[$special_type]) {
            throw new NotFoundHttpException();
        }

        if (!$def = @$definitions[$id]) {
            throw new NotFoundHttpException();
        }

        if ($esc = $this->findOneBy(['sys_name' => $def['sys_name']])) {
            return $esc;
        }

        $esc                       = new TicketEscalationEntity();
        $esc['title']              = $def['title'];
        $esc['sys_name']           = $def['sys_name'];
        $esc['event_trigger']      = $def['event'];
        $esc['event_trigger_time'] = $def['default_time'];
        $esc['is_enabled']         = false;

        if (App::getContainer()->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            if (@$def['default_template']) {
                $esc->actions->addAction(
                    new SendUserNewEmail(
                        [
                            'template'     => $def['default_new_template'],
                            'do_cc_users'  => false,
                            'from_name'    => 'helpdesk_name',
                            'from_account' => 0,
                            'headers'      => [],
                        ]
                    )
                );
            }
        } else {
            if (@$def['default_template']) {
                $esc->actions->addAction(
                    new SendUserEmail(
                        [
                            'template'     => $def['default_template'],
                            'do_cc_users'  => false,
                            'from_name'    => 'helpdesk_name',
                            'from_account' => 0,
                            'headers'      => [],
                        ]
                    )
                );
            }
        }

        if (@$def['default_status']) {
            $esc->actions->addAction(new SetStatus([
                'status' => $def['default_status'],
            ]));
        }

        if (@$def['terms']) {
            $crit  = new FilterTerms();
            $trans = new LegacyTermsTransformer();
            foreach ($def['terms'] as $term) {
                $crit->addTermFromArray($term);
            }

            $esc->terms = $trans->toLegacyTerms($crit);
        }

        $this->getEntityManager()->persist($esc);
        $this->getEntityManager()->flush();

        return $esc;
    }
}
