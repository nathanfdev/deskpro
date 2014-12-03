<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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
 * @subpackage
 */

namespace Application\FormBundle\Form\Type;

use Application\FormBundle\Form\DataTransformer\ArrayToStringTransformer;
use Application\PersonBundle\Person\Context\CreatePersonContext;
use Application\PersonBundle\Person\PersonFactory;
use Application\DeskPRO\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CcType extends AbstractType
{
    /**
     * @var \Application\PersonBundle\Person\PersonFactory
     */
    private $person_factory;

    public function __construct(PersonFactory $person_factory)
    {
        $this->person_factory = $person_factory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ArrayToStringTransformer());

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreData'));
        $builder->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostsubmit'));
    }

    public function onPreData(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Ticket $ticket */
        $form = $event->getForm();
        $config = $form->getConfig();
        $ticket = $config->getOption('ticket');

        $cc_emails = array();
        foreach ($ticket->getUserParticipants() as $participant) {
            $cc_emails[] = (string)$participant->getEmailAddress();
        }

        $event->setData($cc_emails);
    }

    public function onPostSubmit(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Ticket $ticket */
        $form = $event->getForm();
        $config = $form->getConfig();
        $ticket = $config->getOption('ticket');

        $participants = array();
        $cc_emails = $form->getData();
        foreach ($cc_emails as $email) {
            if ($email = trim($email)) {
                // TODO: rethink this "context" approach, because it makes no sense to make one unless creating a person...
                $new_person_context = new CreatePersonContext('gateway.person'); // used only if email makes new person
                $participants[] = $this->person_factory->getOrCreatePersonByEmail($email, $new_person_context);
            }
        }

        $user_participant_ids = array_map(function (Person $person) {
            return $person->id;
        }, $participants);

        $ticket->setParticipantUserIds($user_participant_ids);
    }

    public function getName()
    {
        return 'deskpro_cc';
    }

    public function getParent()
    {
        return 'text';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(
                array(
                    'ticket'
                )
            )
            ->setAllowedTypes(
                array(
                    'ticket' => 'Application\\DeskPRO\\Entity\\Ticket'
                )
            );
    }
}
 