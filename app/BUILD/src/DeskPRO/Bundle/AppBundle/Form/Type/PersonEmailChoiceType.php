<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PersonEmailChoiceType.
 */
class PersonEmailChoiceType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $person = $event->getForm()->getConfig()->getOption('person');
            // if we receive data that looks like it was for the "deskpro_person_email"
            // form, we can just revert to the primary email of the now-logged-in user.
            if (is_array($data) && array_key_exists('email', $data)) {
                $event->setData((string) $person->getPrimaryEmail()->getId());
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setAllowedTypes('person', Person::class)
            ->setDefaults([
                'class'        => PersonEmail::class,
                'choice_label' => 'email',
                'multiple'     => false,
                'expanded'     => false,
                'required'     => false,
                'empty_data'   => function (FormInterface $form) {
                    $person = $form->getConfig()->getOption('person');
                    // if nothing is selected, use their primary email
                    return (string) $person->getPrimaryEmail()->getId();
                },
                'choices' => function (Options $options) {
                    /** @var \Application\DeskPRO\Entity\Person $person */
                    $person = $options['person'];
                    $emails = $person->getEmails();

                    // traverse to make it an array
                    $ems = [];
                    foreach ($emails as $e) {
                        $ems[$e->getId()] = $e;
                    }

                    return $ems;
                },
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['person'] = $options['person'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'deskpro_person_email_choice';
    }
}
