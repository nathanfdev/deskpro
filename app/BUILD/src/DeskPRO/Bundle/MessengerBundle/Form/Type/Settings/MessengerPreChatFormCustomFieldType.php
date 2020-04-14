<?php

namespace DeskPRO\Bundle\MessengerBundle\Form\Type\Settings;

use Application\DeskPRO\Entity\CustomDefChat;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\EntityToIdTransformer;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\PreChatFormCustomField;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MessengerPreChatFormCustomFieldType.
 */
class MessengerPreChatFormCustomFieldType extends AbstractType
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
            ->add('id', EntityType::class, [
                'class' => CustomDefChat::class,
            ])
            ->add('enabled', ApiBooleanType::class)
            ->add('displayOrder', NumberType::class)
        ;

        $builder
            ->get('id')
            ->addModelTransformer(new ReversedTransformer(new EntityToIdTransformer(
                $this->em->getRepository(CustomDefChat::class)
            )))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'         => PreChatFormCustomField::class,
                'allow_extra_fields' => true,
            ])
        ;
    }
}
