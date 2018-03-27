<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants;

use DeskPRO\Bundle\AppBundle\Form\DataTransformer\ArrayToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketParticipantsWebType.
 */
class TicketParticipantsWebType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onTransformToArray'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TicketParticipantsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $transformer  = new ArrayToStringTransformer();
        $participants = [];
        foreach ($form->all() as $child) {
            $participants[] = $child->get('person_email')->getData();
        }

        $view->vars = array_replace($view->vars, [
            'compound' => false,
            'value'    => $transformer->transform($participants),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inline'      => true,
            'constraints' => new Assert\All([
                'constraints' => new Assert\Email([
                    'strict' => true,
                ]),
            ]),
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onTransformToArray(FormEvent $event)
    {
        $transformer = new ArrayToStringTransformer();
        $event->setData($transformer->reverseTransform($event->getData()));
    }
}
