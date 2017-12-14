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
