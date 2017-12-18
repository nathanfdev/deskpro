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

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Error\FormValidatorChecker;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class BaseMassActionsType.
 */
class BaseMassActionsType extends AbstractType
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * Constructor.
     *
     * @param FormFactory $formFactory
     */
    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $subType = $this->formFactory->create($options['params_class'], null, [
            'person' => $options['person'],
        ]);

        $builder
            ->add('ids', EntityType::class, [
                'class'       => $subType->getConfig()->getOption('data_class'),
                'multiple'    => true,
                'required'    => true,
                'constraints' => [
                    new Assert\Count(['min' => 1]),
                    new AppAssert\Permission([
                        'action' => PermissionGroupVoter::MODIFY,
                    ]),
                ],
            ])
            ->add('params', $options['params_class'], [
                'required' => true,
                'person'   => $options['person'],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onForceRequiredFields']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onClearNotMappedErrors'], -1);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['params_class', 'person'])
            ->setAllowedTypes('params_class', 'string')
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onForceRequiredFields(FormEvent $event)
    {
        $data = $event->getData();
        if (!is_array($data)) {
            return;
        }

        if (!isset($data['ids'])) {
            $data['ids'] = [];
        }

        $event->setData($data);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onClearNotMappedErrors(FormEvent $event)
    {
        $data = $event->getData();
        if (is_object($data) && $data->getId()) {
            return;
        }

        FormValidatorChecker::clearFormErrors($event->getForm(), false);
    }
}
