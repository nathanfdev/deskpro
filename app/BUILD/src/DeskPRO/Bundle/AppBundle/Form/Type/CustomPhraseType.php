<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomPhraseType extends AbstractType
{
    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * CustomPhraseType constructor.
     *
     * @param LanguageManager $languageManager
     */
    public function __construct(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
        ;
        $languages = $this->languageManager->getEnabledLanguages();

        foreach ($languages as $language) {
            $builder->add('phrase_'.$language->getLocale(), TextType::class);
        }
    }
}
