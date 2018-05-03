<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat;

use DeskPRO\Bundle\AppBundle\Form\Type\BlobAuthType;
use DeskPRO\Bundle\AppBundle\Form\Type\HtmlTextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChatMessageType.
 */
class ChatMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', HtmlTextareaType::class)
            ->add('attachments', CollectionType::class, [
                'type'         => BlobAuthType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'required'     => false,
            ])
        ;
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
