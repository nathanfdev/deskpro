<?php

namespace Application\AgentBundle\Form\Type;

use Application\DeskPRO\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class NewArticle extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //------------------------------
        // Basic fields
        //------------------------------

        $builder->add('title', 'text');
        $builder->add('content', 'textarea', [
            'filter_clean' => false,
            'required'     => true,
        ]);
        $builder->add('content_input', 'textarea', [
            'filter_clean' => false,
        ]);

        $builder->add('category_id', 'text');
        $builder->add('language_id', 'text');
        $builder->add('brand', 'text', [
            'mapped' => false,
        ]);
        $builder->add('status', 'text');
        $builder->add('slug', 'text');

        $builder->add('labels', 'collection', [
            'type'         => 'hidden',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);

        $builder->add('attach', 'collection', [
            'type'         => 'hidden',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);

        $builder->add('blob_inline_ids', 'collection', [
            'type'         => 'hidden',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);

        $builder->add('review_interval_count', IntegerType::class, [
            'required' => false,
        ]);

        $builder->add('review_interval_unit', ChoiceType::class, [
            'required'          => false,
            'multiple'          => false,
            'choices_as_values' => true,
            'choices'           => [
                Article::REVIEW_DATE_UNIT_DAYS,
                Article::REVIEW_DATE_UNIT_MONTHS,
                Article::REVIEW_DATE_UNIT_YEARS,
            ],
        ]);
    }

    public function getDefaultOptions(array $options)
    {
        return [
            'data_class' => \Application\AgentBundle\Form\Model\NewArticle::class,
        ];
    }

    public function getName()
    {
        return 'newarticle';
    }
}
