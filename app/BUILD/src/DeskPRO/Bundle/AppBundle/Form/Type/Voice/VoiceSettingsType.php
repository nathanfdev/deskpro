<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Voice;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceGlobalAgentSettingsType.
 */
class VoiceSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('agent_voicemail_timeout', IntegerType::class, [
            'required'    => false,
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\GreaterThanOrEqual(10),
            ],
        ]);
    }
}
