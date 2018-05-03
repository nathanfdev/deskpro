<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketMacro;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketMacroType.
 */
class TicketMacroType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('is_global', ApiBooleanType::class)
            ->add('person', EntityType::class, [
                'class'       => Person::class,
                'required'    => false,
                'constraints' => [
                    new AppAssert\Person\PersonType([
                        'type' => 'agent',
                    ]),
                ],
            ])
            ->add('department', EntityType::class, [
                'class'         => Department::class,
                'required'      => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')->where('p.is_tickets_enabled = true');
                },
            ])
            ->add('actions', TicketMacroActionsType::class)
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TicketMacro::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data instanceof TicketMacro) {
            return;
        }

        // force set global if no assignment
        if (!$data->getPerson() && !$data->getDepartment()) {
            $data->setIsGlobal(true);
        }
    }
}
