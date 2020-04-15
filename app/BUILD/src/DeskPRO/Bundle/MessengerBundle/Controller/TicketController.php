<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsApiType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerTickets;
use DeskPRO\Component\Util\RegexUtils;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ChatController.
 *
 * @ApiModes("all")
 * @ApiUserContext("open")
 * @ApiDoc(
 *     target="createNewTicket",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsApiFullType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Ticket",
 *          "person"="Application\DeskPRO\Entity\Person",
 *          "ticket_view_context"="user",
 *          "ticket_visibility"="new"
 *      }
 *     }
 * )
 * @Rest\Route("/ticket")
 * @Feature("messenger")
 */
class TicketController extends AbstractMessengerController
{
    /**
     * @param Request $request
     * @Rest\Post("")
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return View
     */
    public function createTicketAction(Request $request)
    {
        $newTicketService = $this->get('tickets.messenger_new_ticket');

        $settingsResolver = $this->get('messenger.service.settings_resolver');
        $brand            = $this->get('brand_stack')->getActive()->getBrand();
        $requestData      = $request->request->all();
        $messengerTickets = $settingsResolver->getMessengerSettings($brand)->getTickets();
        $subjectPattern   = '';

        if ($messengerTickets->getSubjectOption() === MessengerTickets::TICKET_SUBJECT_OPTION_PRESET) {
            // just a fallback if subject is not set somehow
            $subjectPattern         = $messengerTickets->getSubject() ?: 'Ticket from {name}';
            $subject                = RegexUtils::safePregReplace('#\{\s*[a-zA-Z0-9]+\s*\}#', $this->getVisitorId($request), $subjectPattern);
            $requestData['subject'] = $subject;
        }

        $ticket = $newTicketService->createNewTicket(
            $request,
            $this->getVisitorId($request),
            $this->getUser(),
            $brand,
            Ticket::CREATED_WEB_PERSON_WIDGET
        );
        $person      = $ticket->getPerson();
        $formOptions = [
            'person'              => $person,
            'csrf_protection'     => false,
            'ticket_view_context' => TicketWithLayoutsContext::VIEW_USER,
            'ticket_visibility'   => TicketWithLayoutsContext::VISIBILITY_NEW,
            'form_type'           => TicketWithLayoutsContext::FORM_TYPE_MESSENGER,
        ];

        $form = $this->container->get('form.factory')->create(
            TicketWithLayoutsApiType::class,
            $ticket,
            $formOptions
        );

        $form->submit($requestData, true);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }
        $person = $ticket->getPerson();

        $email     = $person->getPrimaryEmail();
        $person    = $this->get('data.person')->getPersonForEmail($email->getEmail());
        $guestForm = $this->createForm(TicketWithLayoutsApiType::class, $ticket, $formOptions);

        if ($person) {
            $ticket->setPerson($person);
            $requestData['subject'] = $this->updateSubject($subjectPattern, $ticket, $ticket->getPerson());
            $guestForm->submit($requestData, true);
            if (!$this->getUser() || $this->getUser() instanceof PersonGuest) {
                // if the user is not authorized then don't allow to change person entity
                $this->getManager()->getUnitOfWork()->clearEntityChangeSet(spl_object_hash($ticket->getPerson()));
                // if the user is not authorized then don't allow to change person entity email
                // form configured to set email to `primary_email` field
                if ($person->getPrimaryEmail()) {
                    $this->getManager()->getUnitOfWork()->clearEntityChangeSet(spl_object_hash($person->getPrimaryEmail()));
                }
            }
            $newTicketService->acceptNewTicket($ticket, 'widget');
        } else {
            $requestData['subject'] = $this->updateSubject($subjectPattern, $ticket, $ticket->getPerson());
            $newTicketService->acceptNewTicketForGuest($ticket, $requestData, $guestForm, 'widget');
        }

        // check if ticket was created and then return success response
        if ($ticket->getId()) {
            return View::create(new ApiWrapper($ticket));
        } else {
            $form->addError(new FormError('Unable to save ticket.'));

            throw new InvalidFormException($form);
        }
    }

    /**
     * @param string $subjectPattern
     * @param Ticket $ticket
     * @param Person $person
     *
     * @return mixed
     */
    private function updateSubject($subjectPattern, Ticket $ticket, Person $person)
    {
        $subject = $ticket->getSubject();
        if ($subjectPattern !== '') {
            $subject = RegexUtils::safePregReplace('#\{\s*[a-zA-Z0-9]+\s*\}#', $person->getDisplayName(), $subjectPattern);
            $ticket->setSubject($subject);
        }

        return $subject;
    }
}
