<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Portal;

use Application\DeskPRO\Entity\Article;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\KbSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class KbSettingsType.
 */
class KbSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('knowledgebase_deep_tree', ApiBooleanType::class)
            ->add('require_review_date', ApiBooleanType::class)
            ->add('min_review_date', ApiBooleanType::class)
            ->add('min_review_date_interval', IntegerType::class)
            ->add('min_review_date_unit', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [
                    Article::REVIEW_DATE_UNIT_DAYS,
                    Article::REVIEW_DATE_UNIT_MONTHS,
                    Article::REVIEW_DATE_UNIT_YEARS,
                ],
            ])
            ->add('max_review_date', ApiBooleanType::class)
            ->add('max_review_date_interval', IntegerType::class)
            ->add('max_review_date_unit', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [
                    Article::REVIEW_DATE_UNIT_DAYS,
                    Article::REVIEW_DATE_UNIT_MONTHS,
                    Article::REVIEW_DATE_UNIT_YEARS,
                ],
            ])
            ->add('default_review_date', ApiBooleanType::class)
            ->add('default_review_date_interval', IntegerType::class)
            ->add('default_review_date_unit', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [
                    Article::REVIEW_DATE_UNIT_DAYS,
                    Article::REVIEW_DATE_UNIT_MONTHS,
                    Article::REVIEW_DATE_UNIT_YEARS,
                ],
            ])
            ->add('auto_unpublish_review', ApiBooleanType::class)
            ->add('auto_unpublish_review_interval', IntegerType::class)
            ->add('auto_unpublish_review_unit', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [
                    Article::REVIEW_DATE_UNIT_DAYS,
                    Article::REVIEW_DATE_UNIT_MONTHS,
                    Article::REVIEW_DATE_UNIT_YEARS,
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return AppSettingsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => KbSettings::class,
        ]);
    }
}
