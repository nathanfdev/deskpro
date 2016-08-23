<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use DeskPRO\Bundle\AppBundle\Form\DataTransformer\TermEngineTermTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TermEngineTermType.
 */
class TermEngineTermType extends AbstractType
{
    /**
     * @var TermEngineTermTransformer
     */
    private $transformer;

    /**
     * Constructor.
     *
     * @param TermEngineTermTransformer $transformer
     */
    public function __construct(TermEngineTermTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this->transformer);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'cascade_validation' => true,
            'error_bubbling'     => false,
            'error_mapping'      => [
                'op'      => 'op',
                'options' => 'options',
                'terms'   => 'terms',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'term_engine_term';
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $term_type = isset($data['type']) ? $data['type'] : null;

        $form
            ->add('type', 'text', [
                'error_bubbling' => false,
            ])
            ->add('op', 'text', [
                'error_bubbling' => false,
            ])
            ->add('options', TermEngineTermOptionsType::class, [
                'error_bubbling' => false,
                'term_type'      => $term_type,
            ])
        ;

        if ($term_type === 'composite') {
            if (!$form->has('terms')) {
                $form->add('terms', 'collection', [
                    'type'               => 'term_engine_term',
                    'error_bubbling'     => false,
                    'cascade_validation' => true,
                    'allow_add'          => true,
                    'allow_delete'       => true,
                    'constraints'        => [
                        new Assert\Valid(),
                    ],
                ]);
            }
        }
    }
}
