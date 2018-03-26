<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings\Chat;

use Application\DeskPRO\Entity\CustomDefChat;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\EntityToIdTransformer;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings\WidgetBrandChatCustomField;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetBrandChatCustomFieldType.
 */
class WidgetBrandChatCustomFieldType extends AbstractType
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
            ->add('display_order', IntegerType::class, [
                'property_path' => 'displayOrder',
            ])
            ->add('is_enabled', ApiBooleanType::class, [
                'property_path' => 'isEnabled',
            ])
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
        $resolver->setDefaults([
            'data_class' => WidgetBrandChatCustomField::class,
        ]);
    }
}
