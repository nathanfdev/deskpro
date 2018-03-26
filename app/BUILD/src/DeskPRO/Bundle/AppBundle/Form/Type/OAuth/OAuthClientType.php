<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\OAuth;

use DeskPRO\Bundle\AppBundle\Entity\OAuthClient;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use OAuth2\OAuth2;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OAuthClientType.
 */
class OAuthClientType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('context', ChoiceType::class, [
                'required'          => true,
                'choices_as_values' => true,
                'choices'           => [
                    OAuthClient::CONTEXT_USER,
                    OAuthClient::CONTEXT_AGENT,
                ],
            ])
            ->add('allowed_grant_type', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [
                    OAuth2::GRANT_TYPE_AUTH_CODE,
                    OAuth2::GRANT_TYPE_IMPLICIT,
                ],
            ])
            ->add('redirect_uris', CollectionType::class, [
                'property_path'  => 'redirectUris',
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
                'required'       => true,
            ])
            ->add('is_enabled', ApiBooleanType::class, [
                'required'      => false,
                'property_path' => 'isEnabled',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OAuthClient::class,
        ]);
    }
}
