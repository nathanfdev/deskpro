<?php

namespace Application\DeskPRO\CustomFields\Form\Type;

use Application\DeskPRO\CustomFields\Form\Model\CurrencyField;
use DeskPRO\Bundle\AppBundle\Entity\Currency;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\EntityToIdTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CurrencyFieldType.
 */
class CurrencyFieldType extends AbstractType
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
        $builder->add('currency_id', EntityType::class, [
            'class'         => Currency::class,
            'property_path' => 'currencyId',
            'required'      => false,
            'constraints'   => [
                new Assert\NotNull([
                    'message' => 'Currency field value is required',
                ]),
            ],
        ]);

        $builder
            ->get('currency_id')
            ->addModelTransformer(new ReversedTransformer(new EntityToIdTransformer(
                $this->em->getRepository(Currency::class)
            )))
        ;
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
            'data_class' => CurrencyField::class,
        ]);
    }
}
