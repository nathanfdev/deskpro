<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat;

use DeskPRO\Bundle\AppBundle\Form\Type\UserChat\SetPersonListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChatTranscriptInfoType.
 */
class ChatTranscriptInfoType extends AbstractType
{
    /**
     * @var SetPersonListener
     */
    private $personListener;

    /**
     * Constructor.
     *
     * @param SetPersonListener $personListener
     */
    public function __construct(SetPersonListener $personListener)
    {
        $this->personListener = $personListener;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'property_path' => 'person_name',
                'required'      => false,
            ])
            ->add('email', EmailType::class, [
                'property_path' => 'person_email',
                'constraints'   => [
                    new Assert\NotBlank(),
                    new Assert\Email(['strict' => true]),
                ],
            ])
        ;

        $builder->addEventSubscriber($this->personListener);
        $builder->addEventSubscriber(new AutoSetShouldSentTranscriptListener());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection'               => false,
            'csrf_double_submit_protection' => false,
        ]);
    }
}
