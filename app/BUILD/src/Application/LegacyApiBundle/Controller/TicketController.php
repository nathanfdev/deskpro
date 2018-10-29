<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Application\DeskPRO\Entity\CustomDataBilling;
use Application\DeskPRO\Entity\Ticket as Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\EntityRepository\Ticket as TicketRepository;
use Application\DeskPRO\Tickets\SnippetFormatter;
use Application\DeskPRO\Tickets\TicketDisplay;
use Application\DeskPRO\Tickets\TicketMerge\TicketMerge;
use Application\DeskPRO\Tickets\Util;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\SuperKeyPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Doctrine\Common\Collections\ArrayCollection;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @ApiModes("all")
 */
class TicketController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new SuperKeyPermission(), 'updateTicketDatesAction');

        return $multi;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     *
     * @return Response
     */
    public function newTicketAction()
    {
        if (!$this->person->hasPerm('agent_tickets.create')) {
            throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
        }

        $errors = [];

        $person = false;
        $org    = false;

        $subject = $this->in->getString('subject');
        if ($subject === '') {
            $errors['subject'] = ['required_field', 'subject missing or empty'];
        }

        $message_text = $this->in->getString('message');
        if ($message_text === '') {
            $errors['message'] = ['required_field', 'message missing or empty'];
        }

        if ($this->in->checkIsset('agent_id')) {
            $agentId = $this->in->getUInt('agent_id');
            if ($agentId) {
                $agent = $this->em->getRepository('DeskPRO:Person')->findOneById($agentId);
                if (!$agent || !$agent->is_agent) {
                    $errors['agent_id'] = ['invalid_argument', 'Not an agent'];
                }
            }
        } else {
            $agentId = 0;
        }

        $ticket_manager = $this->container->getTicketManager();
        $ticket         = $ticket_manager->createTicket();

        if ($id = $this->in->getUInt('department_id')) {
            $ticket->setDepartmentId($id);
        }
        if ($id = $this->in->getUInt('category_id')) {
            $ticket->setCategoryId($id);
        }
        if ($id = $this->in->getUInt('agent_team_id')) {
            $ticket->setAgentTeamId($id);
        }
        if ($id = $this->in->getUInt('product_id')) {
            $ticket->setProductId($id);
        }
        if ($id = $this->in->getUInt('priority_id')) {
            $ticket->setPriorityId($id);
        }
        if ($id = $this->in->getUInt('workflow_id')) {
            $ticket->setWorkflowId($id);
        }
        if ($id = $this->in->getUInt('urgency')) {
            $ticket->setUrgency($id);
        }

        if (!$ticket->department) {
            $ticket->department = $this->em->getRepository('DeskPRO:Department')->getDefaultDepartment('ticket');
        }

        $sla_ids = $this->in->getCleanValueArray('sla_ids', 'uint');
        if ($sla_ids) {
            $slas = $this->em->getRepository('DeskPRO:Sla')->getByIds($sla_ids);
            foreach ($slas as $sla) {
                if ($sla->apply_type == 'manual') {
                    $ticket->addSla($sla);
                }
            }
        }

        $ticket->creation_system = Ticket::CREATED_WEB_API;
        $ticket->subject         = $subject;
        $ticket->status          = $this->in->getString('status') ?: 'awaiting_agent';
        if ($agentId) {
            $ticket->agent_id = $agentId;
        }

        $message_blobs = $this->_readTicketMessageAttachments();

        // make this check as late as possible to reduce race conditions
        if ($this->in->checkIsset('person_id')) {
            $person = $this->em->getRepository('DeskPRO:Person')->findOneById($this->in->getInt('person_id'));
            if (!$person) {
                $errors['person_id'] = ['invalid_person', 'Invalid person ID'];
            }
        } elseif ($this->in->checkIsset('person_email')) {
            $email = $this->in->getString('person_email');

            if (!\Orb\Validator\StringEmail::isValueValid($email) || !App::getSystemService('email_address_validator')->isValidUserEmail($email)) {
                $errors['person_email'] = ['invalid_email', 'Invalid email address'];
            } else {
                $person_processor                  = new PersonFromEmailProcessor();
                $person_processor->creation_system = 'web.api';
                $person                            = $person_processor->findPersonByEmailAddress($email, $this->in->getString('person_name'));
                if (!$person) {
                    $person = $person_processor->createPersonByEmailAddress($email, $this->in->getString('person_name'));

                    if ($this->in->checkIsset('person_organization')) {
                        $orgName = $this->in->getString('person_organization');

                        $org = $this->em->getRepository('DeskPRO:Organization')->findOneByName($orgName);
                        if (!$org) {
                            $org         = new \Application\DeskPRO\Entity\Organization();
                            $org['name'] = $orgName;
                        }

                        $person->organization          = $org;
                        $person->organization_position = $this->in->getString('person_organization_position');
                    }

                    $this->em->persist($person);
                    if ($person->organization) {
                        $this->em->persist($person->organization);
                    }
                } elseif ($this->in->getBool('overwrite_person_name') && $this->in->getString('person_name')) {
                    $person->setName($this->in->getString('person_name'));
                    $this->em->persist($person);
                }
            }
        } else {
            $errors['person_id'] = ['required_field', 'person_id or person_email missing'];
        }

        if ($errors) {
            return $this->createApiMultipleErrorResponse($errors);
        }

        if ($id = $this->in->getUInt('language_id')) {
            $ticket->setLanguageId($id);
        } else {
            $ticket->language = $person->getRealLanguage();
        }
        $ticket->person = $person;
        if (!$person->id) {
            $ticket->person_email = $person->getPrimaryEmail();
        }

        $this->em->persist($ticket);

        $message                  = new \Application\DeskPRO\Entity\TicketMessage();
        $message->person          = ($this->in->getBool('message_as_agent') ? $this->person : $person);
        $message->creation_system = \Application\DeskPRO\Entity\TicketMessage::CREATED_WEB_API;

        $formatter    = new SnippetFormatter(App::getContainer()->get('twig'));
        $message_text = $formatter->formatText($message_text, $ticket);

        if ($this->in->getBool('message_is_html')) {
            $message_text     = App::get('deskpro.core.input_cleaner')->clean($message_text, 'html_core');
            $message_text     = \Orb\Util\Strings::trimHtml($message_text);
            $message_text     = \Orb\Util\Strings::prepareWysiwygHtml($message_text);
            $message->message = $message_text;
        } else {
            $message->setMessageText($message_text);
        }
        if ($this->in->getBool('is_note')) {
            $message->is_agent_note = true;
        }
        $this->em->persist($message);
        $ticket->addMessage($message);

        $this->_addTicketMessageAttachments($message_blobs, $ticket, $message);

        // need to ensure we treat things as the message owner
        App::setCurrentPerson($message->person);

        $this->db->beginTransaction();

        try {
            if ($org && !$org->id) {
                $this->em->persist($org);
                $this->em->flush();
            }
            if (!$person->id) {
                $this->em->persist($person);
                $this->em->flush();
            }

            $this->em->persist($ticket);
            $this->em->persist($message);

            $this->em->flush();

            $labels = $this->in->getCleanValueArray('label', 'string', 'discard');
            $ticket->getLabelManager()->setLabelsArray($labels);

            App::setCurrentPerson($this->person);

            $field_manager      = $this->container->getSystemService('ticket_fields_manager');
            $post_custom_fields = $this->getCustomFieldInput();
            if (!empty($post_custom_fields)) {
                $field_manager->saveFormToObject($post_custom_fields, $ticket);
            }

            if ($this->in->getBool('message_as_agent')) {
                $context = $ticket_manager->createAgentExecutorContext($this->person, 'newticket', 'api');
            } else {
                $context = $ticket_manager->createUserExecutorContext($ticket->person, 'newticket', 'api');
            }

            $ticket_manager->saveTicket($ticket, $context);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        if (App::getDb()->isTransactionActive()) {
            $e = new \RuntimeException('WARNING: Unclosed transaction');
            SystemErrorHandler::logException($e, false, 'unclosed_trans_api');
            while (App::getDb()->isTransactionActive()) {
                App::getDb()->commit();
            }
        }

        return $this->createApiCreateResponse(
            ['ticket_id' => $ticket->id],
            $this->generateUrl(
                'api_tickets_ticket',
                ['ticket_id' => $ticket->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function getTicketAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        $data           = $ticket->toApiData();
        $ticket_flagged = $this->em->getRepository('DeskPRO:TicketFlagged')->getFlagForTicket($ticket, $this->person);
        if ($ticket_flagged) {
            $data['flag'] = $ticket_flagged;
        }

        $data = ['ticket' => $data];

        if ($this->in->getBool('with_messages')) {
            $ticket_display = new TicketDisplay($ticket, $this->person);

            $messages = $this->em->getRepository('DeskPRO:TicketMessage')->getTicketMessages($ticket, [
                'with_notes' => true,
                'limit'      => 10,
                'order'      => 'DESC',
            ]);

            $data['messages'] = [];
            foreach ($messages as $m) {
                /* @var TicketMessage $m */
                $msg_data            = $m->toApiData(true);
                $msg_data['message'] = $m->procInlineAttach($msg_data['message']);

                $attach = $ticket_display->getMessageAttachments($m, false);
                if ($attach) {
                    $msg_data['attachments'] = [];
                    foreach ($attach as $a) {
                        $msg_data['attachments'][] = $a->toApiData(true);
                    }
                }

                $data['messages'][] = $msg_data;
            }
        }

        //------------------------------
        // Linked tickets
        //------------------------------

        $linked_tickets = [
            'parent'   => null,
            'siblings' => [],
            'children' => [],
            'count'    => 0,
        ];

        /** @var TicketRepository $ticket_repo */
        $ticket_repo = $this->em->getRepository('DeskPRO:Ticket');

        if ($ticket->parent_ticket && $ticket->parent_ticket->status != 'hidden' && $this->checkPerm($ticket->parent_ticket, 'view')) {
            $linked_tickets['parent'] = $ticket->parent_ticket;

            // Find siblings
            $linked_tickets['siblings'] = $this->permCheckArray(
                $ticket_repo->getLinkedTickets($ticket->parent_ticket),
                'view'
            );
            $linked_tickets['siblings'] = array_filter($linked_tickets['siblings'], function ($t) use ($ticket) {
                if ($t->id == $ticket->id) {
                    return false;
                } else {
                    return true;
                }
            });
        }

        $linked_tickets['children'] = $this->permCheckArray(
            $ticket_repo->getLinkedTickets($ticket),
            'view'
        );

        $linked_tickets['count'] = array_sum([
            $linked_tickets['parent'] ? 1 : 0,
            count($linked_tickets['siblings']),
            count($linked_tickets['children']),
        ]);

        if ($this->in->getBool('with_loaded_linked_tickets')) {
            if ($linked_tickets['parent']) {
                $linked_tickets['parent'] = $linked_tickets['parent']->toApiData(false, true);
            }
            foreach (['siblings', 'children'] as $k) {
                $linked_tickets[$k] = array_map(function ($t) {
                    return $t->toApiData(false, true);
                }, $linked_tickets[$k]);
            }
        } else {
            if ($linked_tickets['parent']) {
                $linked_tickets['parent'] = $linked_tickets['parent']->getId();
            }
            foreach (['siblings', 'children'] as $k) {
                $linked_tickets[$k] = array_map(function ($t) {
                    return $t->getId();
                }, $linked_tickets[$k]);
            }
        }

        $data['linked_tickets'] = $linked_tickets;

        // Full data so we can re-construct an edit-type form
        if ($this->in->getBool('with_display_options')) {
            $display_options = [];

            $all_deps    = $this->container->getDataService('Department')->getRootNodes();
            $ticket_deps = [];
            foreach ($all_deps as $d) {
                if ($d->is_tickets_enabled) {
                    $ticket_deps[] = $d;
                }
            }

            $display_options['departments'] = $this->getApiData(array_values($ticket_deps));
            $display_options['agents']      = $this->getApiData(array_values($this->container->getAgentData()->getAgents()));

            if (App::getSetting('core.use_agent_team')) {
                $display_options['agent_teams'] = $this->getApiData(array_values($this->container->getDataService('AgentTeam')->getTeams()));
            }
            if (App::getSetting('core.use_product')) {
                $display_options['products'] = $this->getApiData(array_values($this->container->getDataService('Product')->getRootNodes()));
            }
            if (App::getSetting('core.use_ticket_category')) {
                $display_options['categories'] = $this->getApiData(array_values($this->container->getDataService('TicketCategory')->getRootNodes()));
            }
            if (App::getSetting('core.use_ticket_priority')) {
                $display_options['priorities'] = $this->getApiData(array_values($this->container->getDataService('TicketPriority')->getAll()));
            }
            if (App::getSetting('core.use_ticket_workflow')) {
                $display_options['workflows'] = $this->getApiData(array_values($this->container->getDataService('TicketWorkflow')->getAll()));
            }
            if ($this->container->getLanguageData()->isMultiLang()) {
                $display_options['languages'] = $this->getApiData(array_values($this->container->getLanguageData()->getAll()));
            }

            $data['display_options'] = $display_options;
        }

        return $this->createApiResponse($data);
    }

    public function permCheckArray($tickets, $check_perm)
    {
        if ($tickets instanceof ArrayCollection) {
            $tickets = $tickets->toArray();
        }

        $self = $this;

        return array_filter($tickets, function ($t) use ($self, $check_perm) {
            return $self->checkPerm($t, $check_perm);
        });
    }

    public function checkPerm($ticket, $check_perm)
    {
        $fail = false;
        if (strpos($check_perm, 'modify_') === 0) {
            $check_perm = str_replace('modify_', '', $check_perm);
            if (!$this->person->PermissionsManager->TicketChecker->canModify($ticket, $check_perm)) {
                $fail = true;
            }
        } elseif ($check_perm == 'delete') {
            if (!$this->person->PermissionsManager->TicketChecker->canDelete($ticket)) {
                $fail = true;
            }
        } elseif ($check_perm == 'reply') {
            if (!$this->person->PermissionsManager->TicketChecker->canReply($ticket)) {
                $fail = true;
            }
        }

        if ($fail) {
            return false;
        }

        return true;
    }

    /**
     * @param int $ticket_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postTicketAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id, 'edit');

        $fields = [
            'department_id' => 'Uint',
            'language_id'   => 'Uint',
            'category_id'   => 'Uint',
            'agent_id'      => 'Uint',
            'agent_team_id' => 'Uint',
            'product_id'    => 'Uint',
            'priority_id'   => 'Uint',
            'workflow_id'   => 'Uint',
            'status'        => 'String',
            'is_hold'       => 'Bool',
            'flag'          => 'string',
            'urgency'       => 'Uint',
        ];

        $editor = App::getApi('tickets')->getTicketEditor($ticket);
        $editor->setPersonContext($this->person);

        $errors = [];

        foreach ($fields as $field => $cleanType) {
            if ($this->in->checkIsset($field)) {
                $value = $this->in->{'get'.$cleanType}($field);
                try {
                    $editor->applyActions([$field => $value]);
                } catch (\InvalidArgumentException $e) {
                    $errors[$field] = ["invalid_argument.$field", $e->getMessage()];
                }
            }
        }

        $subject = $this->in->getString('subject');
        if ($subject) {
            $ticket->subject = $subject;
        }

        $person_id = $this->in->getUInt('person_id');
        if ($person_id) {
            $ticket->setPersonId($person_id);
        }

        if ($this->in->checkIsset('is_locked')) {
            if ($this->in->getBool('is_locked')) {
                $ticket->setLockedByAgent($this->person);
            } else {
                $ticket->setLockedByAgent(null);
            }
        }

        if ($errors) {
            return $this->createApiMultipleErrorResponse($errors);
        }

        $this->db->beginTransaction();

        try {
            $this->em->persist($ticket);

            if ($this->person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
                $post_custom_fields = $this->getCustomFieldInput();
                if (!empty($post_custom_fields)) {
                    $field_manager = $this->container->getSystemService('ticket_fields_manager');
                    $field_manager->saveFormToObject($post_custom_fields, $ticket, true);
                    $this->em->persist($ticket);
                }
            }

            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $ticket_id
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     *
     * @return Response
     */
    public function deleteTicketAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id, 'delete');
        $this->em->getConnection()->beginTransaction();

        try {
            $ticket->setStatus('hidden.deleted');
            $this->em->flush();
            Util::deleteTicketsCallRecords($ticket, $this->em, $this->get('blob.storage'));
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }

        $this->db->replace('tickets_deleted', [
            'ticket_id'     => $ticket->id,
            'by_person_id'  => $this->person->id,
            'new_ticket_id' => 0,
            'reason'        => $this->in->getString('reason'),
            'date_created'  => date('Y-m-d H:i:s'),
        ]);

        if ($this->in->getBool('ban')) {
            foreach ($ticket->person->emails as $email) {
                $email_addy = strtolower($email->email);
                App::getDb()->replace('ban_emails', [
                    'banned_email' => $email_addy,
                    'is_pattern'   => 0,
                ]);
            }
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $ticket_id
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     *
     * @return Response
     */
    public function undeleteTicketAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id, 'delete');

        $this->em->getConnection()->beginTransaction();

        try {
            $ticket->setStatus('awaiting_agent');
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function getTicketLogsAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        $ticket_logs = $this->em->getRepository('DeskPRO:TicketLog')->getLogsForTicket($ticket);
        foreach ($ticket_logs as $key => $log) {
            if ($log->action_type == 'executed_triggers') {
                unset($ticket_logs[$key]);
            } elseif ($log->action_type == 'executed_escalations') {
                unset($ticket_logs[$key]);
            }
        }

        return $this->createApiResponse([
            'logs' => $this->getApiData($ticket_logs),
        ]);
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function getTicketMessagesAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        return $this->createApiResponse(['messages' => $this->getApiData($ticket->messages)]);
    }

    /**
     * @param int $ticket_id
     * @param int $message_id
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Response
     */
    public function getTicketMessageAction($ticket_id, $message_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        $message = $this->em->createQuery('
            SELECT m
            FROM DeskPRO:TicketMessage m
            WHERE m.ticket = ?0 AND m.id = ?1
        ')->setParameters([$ticket, $message_id])->setMaxResults(1)->getOneOrNullResult();

        if (!$message) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Message $message_id not found in $ticket_id");
        }

        return $this->createApiResponse(['message' => $message->toApiData()]);
    }

    /**
     * @param int $ticket_id
     * @param int $message_id
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Response
     */
    public function getTicketMessageDetailsAction($ticket_id, $message_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        $message = $this->em->createQuery('
            SELECT m
            FROM DeskPRO:TicketMessage m
            WHERE m.ticket = ?0 AND m.id = ?1
        ')->setParameters([$ticket, $message_id])->setMaxResults(1)->getOneOrNullResult();

        if (!$message) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Message $message_id not found in $ticket_id");
        }

        $email_log = '';
        if ($message->email_source && $message->email_source->source_info) {
            $email_log .= $message->email_source->getSourceInfoAsString()."\n\n";
        }
        if ($message->email_source && $message->email_source->log_blob) {
            $email_log .= $this->container->getBlobStorage()->copyBlobRecordToString($message->email_source->log_blob);
        }
        if (!$email_log) {
            $email_log = null;
        }

        return $this->createApiResponse([
            'unformatted'  => $message->message_text,
            'email_source' => $message->email_source ? $message->email_source->raw_source : null,
            'email_log'    => $email_log,
        ]);
    }

    /**
     * @param int $ticket_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function replyTicketAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id, 'reply');

        if ($this->in->getString('message') === '') {
            return $this->createApiErrorResponse('required_field', 'message cannot be empty');
        }

        $message = new TicketMessage();
        $message->setTicket($ticket);

        if ($pid = $this->in->getUInt('person_id')) {
            if (!$person = $this->em->getRepository('DeskPRO:Person')->find($pid)) {
                throw new NotFoundHttpException();
            }
            $message->setPerson($person);
        } else {
            $message->setPerson($this->in->getBool('message_as_agent') ? $this->person : $ticket->person);
        }

        $message->setIpAddress($this->getRequest()->getClientIp());
        $message->setCreationSystem(TicketMessage::CREATED_WEB_API);

        if ($this->in->getBool('dp_is_mobile')) {
            $message->setCreationSystem(TicketMessage::CREATED_MOBILE_AGENT);
        }

        if ($this->in->getBool('message_is_html')) {
            $message_text = Strings::trimHtml($this->in->getHtmlCore('message'));
            $message_text = Strings::prepareWysiwygHtml($message_text);
            $message->setMessageHtml($message_text);
            $message->setOriginalMessage($this->in->getRaw('message'));
        } else {
            $message->setMessageText($this->in->getString('message'));
        }

        if ($this->in->getBool('is_note')) {
            $message['is_agent_note'] = true;
        }

        if ($dupe_message = $this->em->getRepository('DeskPRO:TicketMessage')->checkDupeMessage($message, $ticket)) {
            return $this->createApiResponse([
                'dupe_message' => true,
                'message_id'   => $dupe_message['id'],
            ]);
        }

        $message_blobs = $this->_readTicketMessageAttachments();
        $this->_addTicketMessageAttachments($message_blobs, $ticket, $message);

        $ticket->addMessage($message);

        if ($this->in->getBool('suppress_user_notify')) {
            $ticket->getTicketLogger()->recordExtra('suppress_user_notify', true);
        }

        if ($this->in->getString('status')) {
            $ticket->setStatus($this->in->getString('status'));
        }

        // need to ensure we treat things as the message owner
        App::setCurrentPerson($message->person);

        $this->db->beginTransaction();

        try {
            $this->em->persist($ticket);
            $this->em->flush();
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createApiCreateResponse(
            ['message_id' => $message->id],
            $this->generateUrl(
                'api_tickets_ticket_message',
                ['ticket_id' => $ticket->id, 'message_id' => $message->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    protected function _readTicketMessageAttachments()
    {
        $attachments = $this->request->files->get('attach');
        if (!is_array($attachments)) {
            $attachments = [$attachments];
        }
        $accept = $this->container->getAttachmentAccepter();

        $blobs = [];

        foreach ($attachments as $file) {
            $error = $accept->getError($file, 'agent');
            if (!$error) {
                $blob = $accept->accept($file, false, ['tag' => 'ticket_attachment']);
                if ($blob) {
                    $blobs[] = $blob;
                }
            }
        }

        foreach ($this->in->getCleanValueArray('attach_id') as $blob_id) {
            $blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);
            if ($blob) {
                $blobs[] = $blob;
            }
        }

        return $blobs;
    }

    protected function _addTicketMessageAttachments(array $blobs, Ticket $ticket, \Application\DeskPRO\Entity\TicketMessage $message)
    {
        foreach ($blobs as $blob) {
            $attach           = new \Application\DeskPRO\Entity\TicketAttachment();
            $attach['blob']   = $blob;
            $attach['person'] = $this->person;

            $message->addAttachment($attach);
            $ticket->addAttachment($attach);
        }
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function claimTicketAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id, 'modify_assign_self');

        $ticket->agent_id = $this->person->id;
        $this->em->persist($ticket);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $ticket_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function splitTicketAction($ticket_id)
    {
        $ticket      = $this->_getTicketOr404($ticket_id, 'modify_merge');
        $message_ids = $this->in->getCleanValueArray('message_ids', 'uint', 'discard');
        $subject     = $this->in->getString('subject');

        $split = new \Application\DeskPRO\Tickets\TicketSplit($ticket);

        try {
            $this->em->beginTransaction();
            $new_ticket = $split->split($subject, $message_ids);
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        $this->em->flush();

        return $this->createApiResponse([
            'success'            => true,
            'ticket_id'          => $new_ticket ? $new_ticket['id'] : null,
            'old_ticket_deleted' => $split->wasOldTicketDeleted(),
        ]);
    }

    /**
     * @param int $ticket_id
     * @param int $merge_ticket_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function mergeTicketAction($ticket_id, $merge_ticket_id)
    {
        $ticket       = $this->_getTicketOr404($ticket_id, 'modify_merge');
        $other_ticket = $this->_getTicketOr404($merge_ticket_id, 'modify_merge');

        try {
            $this->em->beginTransaction();
            $merge = new TicketMerge($this->person, $ticket, $other_ticket);
            $merge->merge();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $ticket_id
     * @param int $link_ticket_id
     *
     * @return Response
     */
    public function linkTicketAction($ticket_id, $link_ticket_id)
    {
        $ticket       = $this->_getTicketOr404($ticket_id);
        $other_ticket = $this->_getTicketOr404($link_ticket_id);

        $this->in->getBool('is_parent')
            ? $ticket->parent_ticket       = $other_ticket
            : $other_ticket->parent_ticket = $ticket;

        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $ticket_id
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     *
     * @return Response
     */
    public function spamTicketAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id, 'delete');

        $this->em->getConnection()->beginTransaction();

        try {
            $ticket->setStatus('hidden.spam');
            $this->em->flush();
            Util::deleteTicketsCallRecords($ticket, $this->em, $this->get('blob.storage'));
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }

        if ($this->in->getBool('ban')) {
            foreach ($ticket->person->emails as $email) {
                $email_addy = strtolower($email->email);
                App::getDb()->replace('ban_emails', [
                    'banned_email' => $email_addy,
                    'is_pattern'   => 0,
                ]);
            }
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $ticket_id
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     *
     * @return Response
     */
    public function unspamTicketAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id, 'delete');

        $this->em->getConnection()->beginTransaction();

        try {
            $ticket->setStatus('awaiting_agent');
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function lockTicketAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        if ($ticket->hasLock()) {
            return $this->createApiErrorResponse('action_impossible', 'Ticket already locked');
        }

        $ticket->setLockedByAgent($this->person);
        $this->em->persist($ticket);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function unlockTicketAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        if ($ticket->hasLock()) {
            $ticket->setLockedByAgent(null);
            $this->em->persist($ticket);
            $this->em->flush();
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function getTicketTasksAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        $tasks = $this->em->getRepository('DeskPRO:Task')->findLinkedTicketTasks($ticket, $this->person);

        return $this->createApiResponse(['tasks' => $this->getApiData($tasks)]);
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function postTicketTasksAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        $title = $this->in->getString('title');
        if (!$title) {
            return $this->createApiErrorResponse('required_field.title', 'title is empty or missing');
        }

        $task                 = new \Application\DeskPRO\Entity\Task();
        $task->title          = $title;
        $task->person         = $this->person;
        $task->assigned_agent = $this->person;

        $assoc         = new \Application\DeskPRO\Entity\TaskAssociatedTicket();
        $assoc->ticket = $ticket;
        $assoc->task   = $task;
        $task->task_associations->add($assoc);

        $this->em->persist($task);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $task->id],
            $this->generateUrl(
                'api_tasks_task',
                ['task_id' => $task->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function getTicketBillingChargesAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        $charges = $ticket->charges;

        $time          = 0;
        $charge_amount = 0;

        foreach ($charges as $charge) {
            $time += $charge->charge_time;
            $charge_amount += $charge->amount;
        }

        return $this->createApiResponse([
            'total_charge_time'   => $time,
            'total_charge_amount' => $charge_amount,
            'total'               => count($charges),
            'charges'             => $this->getApiData($charges),
        ]);
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function postTicketBillingChargesAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        $time   = $this->in->getUInt('time');
        $amount = $this->in->getUFloat('amount');

        if (!$time && !$amount) {
            return $this->createApiErrorResponse('required_field', 'time or amount is required');
        }

        if ($time) {
            $amount = null;
        } else {
            $time = null;
        }

        $charge = $ticket->addCharge($this->person, $time, $amount);
        $this->em->persist($ticket);
        $this->em->flush();

        if ($comment = $this->in->getString('comment')) {
            if ($field = $this->em->getRepository('DeskPRO:CustomDefBilling')->findOneBy(['title' => 'Comment'])) {
                $data = new CustomDataBilling();
                $data->setField($field);
                $data->setRootField($field);
                $data->setValue(0);
                $data->setInput($comment);
                $data->ticket_charge = $charge;
                $this->em->persist($data);
                $this->em->flush();
            }
        }

        return $this->createApiCreateResponse(
            ['id' => $charge->id],
            $this->generateUrl(
                'api_tickets_ticket_billing_charge',
                ['ticket_id' => $ticket->id, 'charge_id' => $charge->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $ticket_id
     * @param int $charge_id
     *
     * @return Response
     */
    public function getTicketBillingChargeAction($ticket_id, $charge_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        $charge = false;

        foreach ($ticket->charges as $ticket_charge) {
            if ($ticket_charge->id == $charge_id) {
                $charge = $ticket_charge;
                break;
            }
        }

        return $this->createApiResponse(['exists' => (bool) $charge]);
    }

    /**
     * @param int $ticket_id
     * @param int $charge_id
     *
     * @return Response
     */
    public function deleteTicketBillingChargeAction($ticket_id, $charge_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        foreach ($ticket->charges as $key => $ticket_charge) {
            if ($ticket_charge->id == $charge_id) {
                $ticket->charges->remove($key);
                $this->em->persist($ticket);
                $this->em->flush();
                break;
            }
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function getTicketSlasAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        return $this->createApiResponse([
            'ticket_slas' => $this->getApiData($ticket->ticket_slas),
        ]);
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function postTicketSlasAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id, 'modify_slas');

        $sla_id = $this->in->getUInt('sla_id');
        $sla    = $this->em->getRepository('DeskPRO:Sla')->find($sla_id);
        if (!$sla) {
            return $this->createApiErrorResponse('invalid_argument.sla_id', 'SLA not found');
        }
        if ($sla->apply_type != 'manual') {
            return $this->createApiErrorResponse('invalid_argument.sla_id', 'no permission to add that SLA');
        }

        $ticket_sla = $ticket->addSla($sla);
        $this->em->persist($ticket);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $ticket_sla->id],
            $this->generateUrl(
                'api_tickets_ticket_sla',
                ['ticket_id' => $ticket->id, 'ticket_sla_id' => $ticket_sla->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $ticket_id
     * @param int $ticket_sla_id
     *
     * @return Response
     */
    public function getTicketSlaAction($ticket_id, $ticket_sla_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        $exists = false;

        foreach ($ticket->ticket_slas as $ticket_sla) {
            if ($ticket_sla->id == $ticket_sla_id) {
                $exists = true;
                break;
            }
        }

        return $this->createApiResponse(['exists' => $exists]);
    }

    /**
     * @param int $ticket_id
     * @param int $ticket_sla_id
     *
     * @return Response
     */
    public function deleteTicketSlaAction($ticket_id, $ticket_sla_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id, 'modify_slas');

        foreach ($ticket->ticket_slas as $key => $ticket_sla) {
            if ($ticket_sla->id == $ticket_sla_id) {
                if ($ticket_sla->sla->apply_type != 'manual') {
                    return $this->createApiErrorResponse('invalid_argument', 'do not have permission to remove ticket SLA '.$ticket_sla_id);
                }

                $ticket->ticket_slas->remove($key);
                $this->em->persist($ticket);
                $this->em->flush();
                break;
            }
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function getParticipantsAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        return $this->createApiResponse(['participants' => $this->getApiData($ticket->participants)]);
    }

    /**
     * @param int $ticket_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function postParticipantsAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id, 'modify_cc');

        $person = null;
        if ($this->in->getUInt('person_id')) {
            $person = $this->em->find('DeskPRO:Person', $this->in->getUInt('person_id'));
        } elseif ($email_address = $this->in->getString('email')) {
            if (!\Orb\Validator\StringEmail::isValueValid($email_address) || App::$container->getEmailAccountManager()->findAccountForEmailAddress($email_address)) {
                return $this->createApiErrorResponse('invalid_email', 'Invalid email address');
            }

            $person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email_address);

            if (!$person) {
                $person = new \Application\DeskPRO\Entity\Person();
                $person->setEmail($email_address);
            }
        } else {
            return $this->createApiErrorResponse('required_field', 'person_id or email must be provided');
        }

        if (!$person) {
            return $this->createApiErrorResponse('not_found', 'Person not found');
        }

        if ($person->id && $person->id == $ticket->person->id) {
            return $this->createApiErrorResponse('owner', 'person is already the owner of the ticket');
        }

        if ($person->id && $ticket->hasParticipantPerson($person)) {
            return $this->createApiCreateResponse(
                ['person_id' => $person->id],
                $this->generateUrl(
                    'api_tickets_ticket_participant',
                    ['ticket_id' => $ticket->id, 'person_id' => $person->id],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );
        }

        $this->db->beginTransaction();

        try {
            if (!$person->id) {
                $this->em->persist($person);
                $this->em->flush();
            }

            $part = $ticket->addParticipantPerson($person);
            if ($part) {
                $this->em->persist($part);
            }
            $this->em->persist($ticket);
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createApiCreateResponse(
            ['person_id' => $part->person->id],
            $this->generateUrl(
                'api_tickets_ticket_participant',
                ['ticket_id' => $ticket->id, 'person_id' => $part->person->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $ticket_id
     * @param int $person_id
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Response
     */
    public function getParticipantAction($ticket_id, $person_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);
        $person = $this->em->find('DeskPRO:Person', $person_id);

        if (!$person) {
            return $this->createApiResponse(['exists' => false]);
        }

        $part = $this->em->createQuery('
            SELECT part
            FROM DeskPRO:TicketParticipant part
            WHERE part.ticket = ?0 AND part.person = ?1
        ')->setParameters([$ticket, $person])->setMaxResults(1)->getOneOrNullResult();

        if (!$part) {
            return $this->createApiResponse(['exists' => false]);
        }

        return $this->createApiResponse(['exists' => true]);
    }

    /**
     * @param int $ticket_id
     * @param int $person_id
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function deleteParticipantAction($ticket_id, $person_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id, 'modify_cc');
        $person = $this->em->find('DeskPRO:Person', $person_id);

        if (!$person) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $part = $this->em->createQuery('
            SELECT part
            FROM DeskPRO:TicketParticipant part
            WHERE part.ticket = ?0 AND part.person = ?1
        ')->setParameters([$ticket, $person])->setMaxResults(1)->getOneOrNullResult();

        if (!$part) {
            return $this->createSuccessResponse();
        }

        $this->db->beginTransaction();

        try {
            $this->em->remove($part);
            $this->em->flush();
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function getLabelsAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        return $this->createApiResponse(['labels' => $this->getApiData($ticket->labels)]);
    }

    /**
     * @param int $ticket_id
     *
     * @return Response
     */
    public function postLabelsAction($ticket_id)
    {
        $ticket = $this->_getTicketOr404($ticket_id, 'modify_labels');
        $label  = $this->in->getString('label');

        if ($label === '') {
            return $this->createApiErrorResponse('required_field', "Field 'label' missing or empty");
        }

        $ticket->getLabelManager()->addLabel($label);
        $this->em->persist($ticket);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['label' => $label],
            $this->generateUrl(
                'api_tickets_ticket_label',
                ['ticket_id' => $ticket->id, 'label' => $label],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $ticket_id
     * @param int $label
     *
     * @return Response
     */
    public function getLabelAction($ticket_id, $label)
    {
        $ticket = $this->_getTicketOr404($ticket_id);

        if ($ticket->getLabelManager()->hasLabel($label)) {
            return $this->createApiResponse(['exists' => true]);
        } else {
            return $this->createApiResponse(['exists' => false]);
        }
    }

    /**
     * @param int $ticket_id
     * @param int $label
     *
     * @return Response
     */
    public function deleteLabelAction($ticket_id, $label)
    {
        $ticket = $this->_getTicketOr404($ticket_id, 'modify_labels');

        $ticket->getLabelManager()->removeLabel($label);
        $this->em->persist($ticket);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @return Response
     */
    public function getFieldsAction()
    {
        $field_manager = $this->container->getSystemService('ticket_fields_manager');
        $fields        = $field_manager->getFields();

        return $this->createApiResponse(['fields' => $this->getApiData($fields)]);
    }

    /**
     * @return Response
     */
    public function getDepartmentsAction()
    {
        $department_list = $this->em->getRepository('DeskPRO:Department')->findAll();
        $departments     = $this->em->getRepository('DeskPRO:Department')->getFlatHierarchy();
        foreach ($department_list as $department) {
            if (!$department->is_tickets_enabled) {
                unset($departments[$department->id]);
            }
        }

        return $this->createApiResponse(['departments' => $departments]);
    }

    /**
     * @return Response
     */
    public function getProductsAction()
    {
        $products = $this->em->getRepository('DeskPRO:Product')->getFlatHierarchy();

        return $this->createApiResponse(['products' => $products]);
    }

    /**
     * @return Response
     */
    public function getCategoriesAction()
    {
        $categories = $this->em->getRepository('DeskPRO:TicketCategory')->getFlatHierarchy();

        return $this->createApiResponse(['categories' => $categories]);
    }

    /**
     * @return Response
     */
    public function getPrioritiesAction()
    {
        $priorities = $this->em->createQuery('
            SELECT p
            FROM DeskPRO:TicketPriority p
            ORDER BY p.priority
        ')->execute();

        return $this->createApiResponse(['priorities' => $this->getApiData($priorities)]);
    }

    /**
     * @return Response
     */
    public function getWorkflowsAction()
    {
        $workflows = $this->em->createQuery('
            SELECT w
            FROM DeskPRO:TicketWorkflow w
            ORDER BY w.display_order
        ')->execute();

        return $this->createApiResponse(['workflows' => $this->getApiData($workflows)]);
    }

    /**
     * @return Response
     */
    public function getSlasAction()
    {
        $slas = $this->em->getRepository('DeskPRO:Sla')->getAllSlas();

        return $this->createApiResponse(['slas' => $this->getApiData($slas)]);
    }

    /**
     * @param int $sla_id
     *
     * @return Response
     */
    public function getSlaAction($sla_id)
    {
        $sla = $this->em->getRepository('DeskPRO:Sla')->find($sla_id);
        if (!$sla) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no SLA with ID $sla_id");
        }

        return $this->createApiResponse(['sla' => $sla->toApiData()]);
    }

    /**
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Application\DeskPRO\Entity\Ticket
     */
    protected function _getTicketOr404($id, $check_perm = null)
    {
        $q = $this->em->createQuery('SELECT t FROM DeskPRO:Ticket t WHERE t.id = ?0');
        $q->setFetchMode('DeskPRO:Person', 'person', 'EAGER');
        $q->setFetchMode('DeskPRO:Person', 'agent', 'EAGER');
        $q->setParameters([$id]);

        $ticket = $q->getOneOrNullResult();

        if (!$ticket) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no ticket with ID $id");
        }

        if (!$this->person->PermissionsManager->TicketChecker->canView($ticket)) {
            throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
        }

        if ($check_perm && !$this->checkTicketPerm($ticket, $check_perm)) {
            throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
        }

        return $ticket;
    }

    public function checkTicketPerm(Ticket $ticket, $check_perm)
    {
        if (strpos($check_perm, 'modify_') === 0) {
            $check_perm = str_replace('modify_', '', $check_perm);
            if (!$this->person->PermissionsManager->TicketChecker->canModify($ticket, $check_perm)) {
                return false;
            }
        } elseif ($check_perm == 'delete') {
            if (!$this->person->PermissionsManager->TicketChecker->canDelete($ticket)) {
                return false;
            }
        } elseif ($check_perm == 'reply') {
            if (!$this->person->PermissionsManager->TicketChecker->canReply($ticket)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $ticket_id
     *
     * @return Response
     */
    public function updateTicketDatesAction($ticket_id, Request $request)
    {
        $fields = json_decode($request->getContent(), 1);
        $vals   = [];

        foreach ($fields as $k => $v) {
            $v      = 0 === strpos($k, 'date_') ? date('Y-m-d H:i:s', strtotime($v)) : (int) $v;
            $vals[] = sprintf('%s = "%s"', $k, $v);
        }

        if ($vals) {
            $this->em->getConnection()->executeQuery(sprintf(
                'update tickets set %s where id = %d',
                implode(',', $vals), $ticket_id
            ));
        }

        return $this->createJsonResponse($vals);
    }
}
