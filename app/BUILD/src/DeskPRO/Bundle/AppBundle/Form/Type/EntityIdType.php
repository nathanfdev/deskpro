<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Form\DataTransformer\EntityToIdTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EntityIdType
 *
 * @package DeskPRO\Bundle\AppBundle\Form\Type
 */
class EntityIdType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * EntityIdType constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new EntityToIdTransformer($this->em->getRepository($options['class']), $options['keep_as_ids'])
        );
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'keep_as_ids' => false, // Instead of converting the ids to entities, return ids and preserve the validation part of EntityType
        ]);
    }
}
