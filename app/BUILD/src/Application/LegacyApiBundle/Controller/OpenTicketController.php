<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiModes("all")
 */
class OpenTicketController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        return;
    }

    public function newTicketMessageAction()
    {
        $ticket_manager = $this->container->getTicketManager();

        if (!\Orb\Validator\StringEmail::isValueValid($this->in->getString('email')) || App::$container->getEmailAccountManager()->findAccountForEmailAddress($this->in->getString('email'))) {
            return $this->createApiErrorResponse('invalid_email', 'The email address supplied is invalid');
        }
        if (!$this->in->getString('subject')) {
            return $this->createApiErrorResponse('invalid_subject', 'The subject was empty');
        }
        if (!$this->in->getString('message')) {
            return $this->createApiErrorResponse('invalid_message', 'The message was empty');
        }

        if ($tac = $this->in->getString('tac')) {
            $ticket = $this->em->getRepository('DeskPRO:Ticket')->getByAccessCode($tac);

            if (!$ticket || !$ticket->getProperty('allow_send_reply_service')) {
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
            }

            $ticket_manager->markAsManaged($ticket);

            $person = App::getOrm()->getRepository('DeskPRO:Person')->findOneByEmail($this->in->getString('email'));
            if (!$person) {
                $person = Person::newContactPerson([
                    'email' => $this->in->getString('email'),
                    'name'  => $this->in->getString('name'),
                ]);
                $this->em->persist($person);
            }

            $context = $ticket_manager->createUserExecutorContext($ticket->person, 'newreply', 'api');
        } else {
            // Not allowed to create new tickets using this service
            if (!$this->apikey && !$this->api_token) {
                $response = $this->createApiErrorResponse('invalid_auth', 'Please provide a valid API key or token', 401);
                $response->headers->add([
                    'WWW-Authenticate' => 'Basic realm="API"',
                ]);

                return $response;
            }

            $person = App::getOrm()->getRepository('DeskPRO:Person')->findOneByEmail($this->in->getString('email'));
            if (!$person) {
                $person = Person::newContactPerson([
                    'email' => $this->in->getString('email'),
                    'name'  => $this->in->getString('name'),
                ]);
                $this->em->persist($person);
            }

            $ticket                    = $ticket_manager->createTicket();
            $ticket['creation_system'] = Ticket::CREATED_WEB_API;
            $ticket['person']          = $person;
            $ticket['subject']         = $this->in->getString('subject');
            $ticket->setProperty('allow_send_reply_service', true);

            $ticket->getTicketLogger()->recordExtra('suppress_user_notify', true);
            $ticket->getTicketLogger()->recordExtra('suppress_agent_notify', true);

            if ($my_tac = $this->in->getString('my_tac')) {
                $ticket->setProperty('send_reply_tac', $my_tac);
            }
            if ($reply_service_url = $this->in->getString('my_reply_service')) {
                $ticket->setProperty('send_reply_service', $reply_service_url);
            }

            $context = $ticket_manager->createUserExecutorContext($ticket->person, 'newticket', 'api');
        }

        $message_html = $this->in->getHtmlCore('message');

        $ticket_message           = new TicketMessage();
        $ticket_message['person'] = $person;
        $ticket_message->setMessageHtml($message_html);

        $ticket->addMessage($ticket_message);
        $ticket['status'] = 'awaiting_agent';

        $this->em->persist($ticket);
        $this->em->persist($ticket_message);

        $ticket_manager->saveTicket($ticket, $context);

        $this->em->flush();

        return $this->createJsonResponse([
            'success' => true,
            'tac'     => $ticket->getAccessCode(),
        ]);
    }
}
