<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use DeskPRO\Bundle\AppBundle\Entity\DirectMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DirectMessageReplyType.
 */
class DirectMessageReplyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', TextareaType::class, [
                'property_path' => 'messageHtml',
                'label'         => 'Reply',
                'required'      => true,
                'constraints'   => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
            ])
        ;

//        $builder->get('message')
//            ->addModelTransformer(new CallbackTransformer(
//                function ($value) {
//                    return $value;
//                },
//                function ($value) {

//                    $s = '""""';
//                    $b = '"';
//                    $m = '';
//                    if ($value == '""') {
//                        return '';
//                    }
//                    return nl2br($value);
//                }
//            ))
//        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => DirectMessage::class,
            ])
        ;
    }
}
