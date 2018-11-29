<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\UserChat;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AbstractUserChatQueueTarget;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class UserChatQueueTargetType.
 */
class UserChatQueueTargetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'mapped'            => false,
                'choices_as_values' => true,
                'choices'           => [
                    AbstractUserChatQueueTarget::TYPE_AGENT,
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // create entity instance by type
        $target     = null;
        $targetType = isset($data['type']) ? $data['type'] : null;
        if ($targetType) {
            $target = AbstractUserChatQueueTarget::createInstanceByType($targetType);
        }

        // force data submission
        if ($targetType) {
            if (!isset($data['target'])) {
                $data['target'] = null;
            }
        }

        $event->setData($data);
        $form->setData($target);

        // set form fields
        switch ($targetType) {
            case AbstractUserChatQueueTarget::TYPE_AGENT:
                $form->add('target', EntityType::class, [
                    'property_path' => 'agent',
                    'class'         => Person::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')->where(
                            'u.is_agent = 1',
                            'u.is_deleted = 0',
                            'u.is_disabled = 0'
                        );
                    },
                ]);

                break;
        }

        if ($form->has('target')) {
            $form->add('sort', IntegerType::class, [
                'required'      => false,
                'property_path' => 'sort',
                'empty_data'    => '10',
            ]);
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data instanceof AbstractUserChatQueueTarget) {
            $event->setData(null);
        }
    }
}
