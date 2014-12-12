<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Form\Type;


use Application\DeskPRO\Entity\PersonEmail;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class PersonManageEmailsType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {

        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('emails', 'collection', array(
            'type' => 'deskpro_person_email',
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'label' => false,
            'by_reference' => false,
            'options' => array(
                'label' => false,
                'required' => false,
                'email_constraints' => array(
                    new Email(array('message' => 'This email adddress is not valid')),
                )
            )
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $person = $event->getData();

            $email = new PersonEmail();
            $email->setPerson($person);

            $person->emails->add($email);
        });

        $em = $this->em;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($em) {
            $form = $event->getForm();
            $person = $event->getData();

            // clean up attachments that don't have a blob (delete them from the message)
            foreach ($person->emails as $person_email) {
                if (!$person_email->email) {
                    $person->emails->removeElement($person_email);
                } else {
                    $em->persist($person_email);
                }
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Application\DeskPRO\Entity\Person'
            )
        );

        $resolver->setRequired(
            array('settings')
        );

        $resolver->setAllowedTypes(
            array(
                'settings' => 'Application\DeskPRO\NewSettings\SettingsBag'
            )
        );
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'person_manage_emails';
    }
}
