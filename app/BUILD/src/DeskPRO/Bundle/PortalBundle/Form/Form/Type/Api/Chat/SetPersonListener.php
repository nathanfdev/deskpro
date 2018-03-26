<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat;

use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class SetPersonListener.
 */
class SetPersonListener implements EventSubscriberInterface
{
    /**
     * @var EmailAccountManager
     */
    protected $accountManager;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor.
     *
     * @param EmailAccountManager $accountManager
     * @param EntityManager       $em
     */
    public function __construct(EmailAccountManager $accountManager, EntityManager $em)
    {
        $this->accountManager = $accountManager;
        $this->em             = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    /**
     * Check for existing person and assign to chat.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        /** @var ChatConversation $conversation */
        $conversation = $event->getData();
        $email        = $conversation->getPersonEmail();

        /** @var \Application\DeskPRO\EntityRepository\Person $repository */
        $repository = $this->em->getRepository(Person::class);
        $person     = $repository->findOneByEmail($email);

        if ($this->accountManager->findAccountForEmailAddress($email)) {
            return;
        }

        if (!$person && $email) {
            $person = new Person();
            $person->setName($conversation->getPersonName());
            $person->setEmail($email);
        } elseif (!$email) {
            return;
        }

        $enteredName = $conversation->getPersonName();
        $conversation->setPerson($person);

        // Person entity will overwrite custom person name entered in the form
        // If user entered custom name then set it back
        if ($enteredName) {
            $conversation->setPersonName($enteredName);
        }
    }
}
