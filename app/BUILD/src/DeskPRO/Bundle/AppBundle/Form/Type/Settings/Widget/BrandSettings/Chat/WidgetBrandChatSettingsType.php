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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings\Chat;

use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\EntityToIdTransformer;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings\WidgetBrandChatSettings;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetBrandChatSettingsType.
 */
class WidgetBrandChatSettingsType extends AbstractType
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
        $builder
            ->add('request_user_info', ApiBooleanType::class, [
                'property_path' => 'requestUserInfo',
            ])
            ->add('proactive', ApiBooleanType::class)
            ->add('begin_mode', ChoiceType::class, [
                'property_path'     => 'beginMode',
                'choices_as_values' => true,
                'choices'           => [
                    WidgetBrandChatSettings::BEGIN_MODE_CONVERSATION,
                    WidgetBrandChatSettings::BEGIN_MODE_FORM,
                ],
            ])
            ->add('waiting_timeout', IntegerType::class, [
                'property_path' => 'waitingTimeout',
            ])
            ->add('popup', WidgetBrandChatPopupSettingsType::class)
            ->add('select_department', ChoiceType::class, [
                'property_path'     => 'selectDepartment',
                'choices_as_values' => true,
                'choices'           => [
                    WidgetBrandChatSettings::SELECT_DEFAULT,
                    WidgetBrandChatSettings::SELECT_CUSTOM,
                ],
            ])
            ->add('default_department', EntityType::class, [
                'property_path' => 'defaultDepartment',
                'class'         => Department::class,
            ])
            ->add('required_name', ApiBooleanType::class, [
                'property_path' => 'requiredName',
            ])
            ->add('required_email', ApiBooleanType::class, [
                'property_path' => 'requiredEmail',
            ])
        ;

        $departmentRepo = $this->em->getRepository(Department::class);
        $builder
            ->get('default_department')
            ->addModelTransformer(new ReversedTransformer(new EntityToIdTransformer($departmentRepo)))
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onClearDefaultDepartment']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WidgetBrandChatSettings::class,
        ]);
    }

    /**
     * If we select custom department then clear default department value.
     * Uses to prevent cases where stored department was already deleted and we get validation error for hidden field.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onClearDefaultDepartment(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['select_department']) && $data['select_department'] === WidgetBrandChatSettings::SELECT_CUSTOM) {
            $data['default_department'] = null;
        }

        $event->setData($data);
    }
}
