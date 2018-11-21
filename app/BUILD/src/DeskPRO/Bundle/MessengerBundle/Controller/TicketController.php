<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\Tickets\ExecutorContext;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsApiFullType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\MessengerBundle\Exception\MessengerApiException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
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
        $ticket = new Ticket();

        $requestData = $request->request->all();
        $formOptions = [
            'ticket_view_context' => TicketWithLayoutsContext::VIEW_USER,
            'ticket_visibility'   => TicketWithLayoutsContext::VISIBILITY_NEW,
        ];

        // try to find person
        /** @var PersonRepository $personRepository */
        $personRepository = $this
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository(Person::class);
        $person = null;

        if (isset($requestData['person_id'])) {
            $person = $this->get('doctrine.orm.default_entity_manager')->find(Person::class, $request['person_id']);
            unset($requestData['person_id']);
            if (isset($requestData['email'])) {
                unset($requestData['email']);
            }
        }

        if (!$person && isset($requestData['email'])) {
            $person = $personRepository->findOneByEmail($requestData['email']);
            unset($requestData['email']);
        }

        $errors = [];
        if (!$person && !isset($request['email'])) {
            $errors['email']     = 'Either email or person_id parameter is required';
            $errors['person_id'] = 'Either email or person_id parameter is required';
        }

        if ($errors) {
            throw new MessengerApiException($errors);
        }

        $form = $this->container->get('form.factory')->create(
            TicketWithLayoutsApiFullType::class,
            $ticket,
            $formOptions
        );

        $form->submit($requestData, false);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $manager = $this->getContainer()->getTicketManager();
        $context = $manager->createUserExecutorContext($person, ExecutorContext::EVENT_NEW, ExecutorContext::METHOD_API, ['api_v2' => true]);

        $manager->saveTicket($ticket, $context);

        return View::create(new ApiWrapper($ticket));
    }
}
