<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Error\FormValidatorChecker;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Constructor.
     *
     * @param FormFactory        $formFactory
     * @param ValidatorInterface $validator
     */
    public function __construct(FormFactory $formFactory, ValidatorInterface $validator)
    {
        $this->formFactory = $formFactory;
        $this->validator   = $validator;
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
                'required'    => false,
            ])
            ->add('date_created', DateRangeType::class, [
                'class'         => $subType->getConfig()->getOption('data_class'),
                'date_property' => 'date_created',
                'required'      => false,
            ])
            ->add('params', $options['params_class'], [
                'required' => true,
                'person'   => $options['person'],
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], -1);
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
    public function onSubmit(FormEvent $event)
    {
        $data     = $event->getData();
        $entities = [];

        if (isset($data['ids']) && count($data['ids'])) {
            foreach ($data['ids'] as $entity) {
                $entities[$entity->getId()] = $entity;
            }
        }

        if (isset($data['date_created']) && count($data['date_created'])) {
            foreach ($data['date_created'] as $entity) {
                $entities[$entity->getId()] = $entity;
            }
        }

        $data['entities'] = array_values($entities);
        $event->setData($data);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (is_object($data) && $data->getId()) {
            return;
        }

        // clear unmapped errors
        FormValidatorChecker::clearFormErrors($form, false);

        $hasModifyActions = false;
        $hasDeleteActions = false;

        if ($form->get('params')->has('set_of_actions')) {
            $actions = $form->get('params')->get('set_of_actions')->getData();

            if (is_array($actions)) {
                $hasDeleteActions = in_array('delete', $actions);
                if (array_diff($actions, ['delete'])) {
                    $hasModifyActions = true;
                }
            }
        }

        foreach ($form->get('params')->all() as $name => $paramsForm) {
            if ($name === 'set_of_actions') {
                continue;
            }

            if ($paramsForm->isSubmitted()) {
                $hasModifyActions = true;
            }
        }

        $validateObjects = function ($groupName, $permissionName) use ($form) {
            $violations = $this->validator->validate($form->get($groupName)->getData(), new AppAssert\Permission([
                'action' => $permissionName,
            ]));

            foreach ($violations as $violation) {
                $form->get($groupName)->addError(new FormError(
                    $violation->getMessage(),
                    $violation->getMessageTemplate(),
                    $violation->getParameters(),
                    $violation->getPlural(),
                    $violation
                ));
            }
        };

        $validateCount = function ($groupName, $force = false) use ($form, $data) {
            if (!$force && (!isset($data[$groupName]) || !$form->get($groupName)->isSubmitted())) {
                return;
            }

            $keys = [];
            if (isset($data[$groupName])) {
                if ($data[$groupName] instanceof ArrayCollection) {
                    $keys = $data[$groupName]->getKeys();
                } elseif (is_array($data[$groupName])) {
                    $keys = array_keys($data[$groupName]);
                }
            }

            $violations = $this->validator->validate($keys, new Assert\Count(['min' => 1]));

            foreach ($violations as $violation) {
                $form->get($groupName)->addError(new FormError(
                    $violation->getMessage(),
                    $violation->getMessageTemplate(),
                    $violation->getParameters(),
                    $violation->getPlural(),
                    $violation
                ));
            }
        };

        // check if we have fetched objects
        $validateCount('ids', !isset($data['date_created']));
        $validateCount('date_created');

        // check modify permissions
        if ($hasModifyActions) {
            $validateObjects('ids', PermissionGroupVoter::MODIFY);
            $validateObjects('date_created', PermissionGroupVoter::MODIFY);
        }

        // check delete permissions
        if ($hasDeleteActions) {
            $validateObjects('ids', PermissionGroupVoter::DELETE);
            $validateObjects('date_created', PermissionGroupVoter::DELETE);
        }
    }
}
