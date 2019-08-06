<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Portal;

use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\CommunitySettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommunitySettingsType.
 */
class CommunitySettingsType extends AbstractType
{
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
            'data_class' => CommunitySettings::class,
        ]);
    }
}
