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
                'constraints' => new Assert\Email(),
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
