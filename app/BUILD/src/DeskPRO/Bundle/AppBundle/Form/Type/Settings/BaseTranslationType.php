<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings;

use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\EntityToIdTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;

/**
 * Class BaseTranslationType.
 */
class BaseTranslationType extends AbstractType
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
        $builder->add('language', EntityType::class, [
            'class' => Language::class,
        ]);

        $languageRepository = $this->em->getRepository(Language::class);
        $builder
            ->get('language')
            ->addModelTransformer(new ReversedTransformer(new EntityToIdTransformer($languageRepository)))
        ;
    }
}
