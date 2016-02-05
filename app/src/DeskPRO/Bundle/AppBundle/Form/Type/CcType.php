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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext;
use DeskPRO\Bundle\PortalBundle\Form\Form\DataTransformer\ArrayToStringTransformer;
use DeskPRO\Bundle\PortalBundle\Person\PersonFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class CcType.
 */
class CcType extends AbstractType
{
    /**
     * @var \DeskPRO\Bundle\PortalBundle\Person\PersonFactory
     */
    private $person_factory;

    /**
     * Constructor.
     *
     * @param PersonFactory $person_factory
     */
    public function __construct(PersonFactory $person_factory)
    {
        $this->person_factory = $person_factory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ArrayToStringTransformer());

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreData']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostsubmit']);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreData(FormEvent $event)
    {
        /* @var \Application\DeskPRO\Entity\Ticket $ticket */
        $form   = $event->getForm();
        $config = $form->getConfig();
        $ticket = $config->getOption('ticket');

        $cc_emails = [];
        foreach ($ticket->getUserParticipants() as $participant) {
            $cc_emails[] = (string) $participant->getEmailAddress();
        }

        $event->setData($cc_emails);
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        /* @var \Application\DeskPRO\Entity\Ticket $ticket */
        $form   = $event->getForm();
        $config = $form->getConfig();
        $ticket = $config->getOption('ticket');

        $participants = [];
        $cc_emails    = $form->getData();
        foreach ($cc_emails as $email) {
            $email = trim($email);

            if (strlen($email) === 0) {
                continue;
            }

            if (!preg_match('/.+\@.+\..+/', $email)) {
                $form->addError(new FormError(sprintf('Invalid email detected: "%s". Please review the email list.', $email)));
                continue;
            }

            $email = trim($email);
            if ($email) {
                $new_person_context = new CreatePersonContext('gateway.person'); // used only if email makes new person
                $participants[]     = $this->person_factory->getOrCreatePersonByEmail($email, $new_person_context);
            }
        }

        $user_participant_ids = array_map(function (Person $person) {
            return $person->id;
        }, $participants);

        // the method below actually persists, and we only want to do that if this field is valid
        if (!count($form->getErrors())) {
            $ticket->setParticipantUserIds($user_participant_ids);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'deskpro_cc';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired([
                'ticket',
            ])
            ->setAllowedTypes([
                'ticket' => 'Application\\DeskPRO\\Entity\\Ticket',
            ])
        ;
    }
}
