<?php

namespace Application\DeskPRO\CustomFields\Form\Type;

use Application\DeskPRO\CustomFields\Form\Model\FileField;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FileFieldType.
 */
class FileFieldType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('multiple', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('user_extensions_limit_mode', ChoiceType::class, [
                'required'          => false,
                'property_path'     => 'userExtensionsLimitMode',
                'choices_as_values' => true,
                'choices'           => [
                    FileField::EXT_LIMIT_MODE_ANY,
                    FileField::EXT_LIMIT_MODE_ALLOW,
                    FileField::EXT_LIMIT_MODE_DISALLOW,
                ],
            ])
            ->add('user_must_extensions', CollectionType::class, [
                'required'      => false,
                'property_path' => 'userMustExtensions',
                'entry_type'    => TextType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
            ])
            ->add('user_not_extensions', CollectionType::class, [
                'required'      => false,
                'property_path' => 'userNotExtensions',
                'entry_type'    => TextType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
            ])
            ->add('user_max_file_size', IntegerType::class, [
                'required'      => false,
                'property_path' => 'userMaxFileSize',
            ])
            ->add('agent_extensions_limit_mode', ChoiceType::class, [
                'required'          => false,
                'property_path'     => 'agentExtensionsLimitMode',
                'choices_as_values' => true,
                'choices'           => [
                    FileField::EXT_LIMIT_MODE_ANY,
                    FileField::EXT_LIMIT_MODE_ALLOW,
                    FileField::EXT_LIMIT_MODE_DISALLOW,
                ],
            ])
            ->add('agent_must_extensions', CollectionType::class, [
                'required'      => false,
                'property_path' => 'agentMustExtensions',
                'entry_type'    => TextType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
            ])
            ->add('agent_not_extensions', CollectionType::class, [
                'required'      => false,
                'property_path' => 'agentNotExtensions',
                'entry_type'    => TextType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
            ])
            ->add('agent_max_file_size', IntegerType::class, [
                'required'      => false,
                'property_path' => 'agentMaxFileSize',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CustomFieldTypeAbstract::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FileField::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (isset($data['user_extensions_limit_mode'])) {
            if ($data['user_extensions_limit_mode'] === FileField::EXT_LIMIT_MODE_ANY) {
                $data['user_must_extensions'] = [];
                $data['user_not_extensions']  = [];
            } elseif ($data['user_extensions_limit_mode'] === FileField::EXT_LIMIT_MODE_ALLOW) {
                $data['user_not_extensions'] = [];
            } elseif ($data['user_extensions_limit_mode'] === FileField::EXT_LIMIT_MODE_DISALLOW) {
                $data['user_must_extensions'] = [];
            }
        }

        if (isset($data['agent_extensions_limit_mode'])) {
            if ($data['agent_extensions_limit_mode'] === FileField::EXT_LIMIT_MODE_ANY) {
                $data['agent_must_extensions'] = [];
                $data['agent_not_extensions']  = [];
            } elseif ($data['agent_extensions_limit_mode'] === FileField::EXT_LIMIT_MODE_ALLOW) {
                $data['agent_not_extensions'] = [];
            } elseif ($data['agent_extensions_limit_mode'] === FileField::EXT_LIMIT_MODE_DISALLOW) {
                $data['agent_must_extensions'] = [];
            }
        }

        $event->setData($data);
    }
}
