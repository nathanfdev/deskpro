<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Form\DataTransformer\HtmlPurifierTransformer;
use Orb\Input\Cleaner\Cleaner;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HtmlTextareaType.
 */
class HtmlTextareaType extends AbstractType
{
    /**
     * @var Cleaner
     */
    private $cleaner;

    /**
     * Constructor.
     *
     * @param Cleaner $cleaner
     */
    public function __construct(Cleaner $cleaner)
    {
        $this->cleaner = $cleaner;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addViewTransformer(new HtmlPurifierTransformer($this->cleaner, $options['html_type']));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'html_type'    => 'html',
            'filter_clean' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextareaType::class;
    }
}
