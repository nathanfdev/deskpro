<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Portal;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\KbSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class KbSettingsType.
 */
class KbSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('knowledgebase_deep_tree', ApiBooleanType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return AppSettingsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => KbSettings::class,
        ]);
    }
}
