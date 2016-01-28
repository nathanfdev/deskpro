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
namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;

class PersonEmailChoiceType extends AbstractType
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
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($options) {
                $data = $event->getData();
                $person = $event->getForm()->getConfig()->getOption('person');
                // if we recieve data that looks like it was for the "deskpro_person_email"
                // form, we can just revert to the primary email of the now-logged-in user.
                if (is_array($data) && array_key_exists('email', $data)) {
                    $event->setData($person->getPrimaryEmail()->getId());
                }
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('person'));

        $resolver->setAllowedTypes(
            array(
                'person' => 'Application\DeskPRO\Entity\Person',
            )
        );

        $resolver->setDefaults(array(
            'class'      => 'Application\DeskPRO\Entity\PersonEmail',
            'property'   => 'email',
            'multiple'   => false,
            'expanded'   => false,
            'required'   => false,
            'empty_data' => function (FormInterface $form) {
                $person = $form->getConfig()->getOption('person');
                // if nothing is selected, use their primary email
                return $person->getPrimaryEmail()->getId();
            },
            'choices' => function (Options $options) {
                /** @var \Application\DeskPRO\Entity\Person $person */
                $person = $options['person'];

                $emails = $person->getEmails();

                // traverse to make it an array
                $ems = array();
                foreach ($emails as $e) {
                    $ems[$e->getId()] = $e;
                }

                return $ems;
            },
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['person'] = $options['person'];
    }

    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'deskpro_person_email_choice';
    }
}
