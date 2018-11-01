<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Reports;

use Application\DeskPRO\Entity\ReportWidget;
use Application\DeskPRO\Translate\Loader\DeskproLoader;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\ReportBundle\Dashboard\DashboardWidgetManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReportWidgetType.
 */
class ReportWidgetType extends AbstractType
{
    /**
     * @var DeskproLoader
     */
    private $phraseLoader;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * Constructor.
     *
     * @param DeskproLoader   $phraseLoader
     * @param LanguageManager $languageManager
     */
    public function __construct(DeskproLoader $phraseLoader, LanguageManager $languageManager)
    {
        $this->phraseLoader    = $phraseLoader;
        $this->languageManager = $languageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('display_types', ChoiceType::class, [
            'choices' => [
                DashboardWidgetManager::WIDGET_RENDER_TYPE_AREA,
                DashboardWidgetManager::WIDGET_RENDER_TYPE_BAR,
                DashboardWidgetManager::WIDGET_RENDER_TYPE_LINE,
                DashboardWidgetManager::WIDGET_RENDER_TYPE_PIE,
                DashboardWidgetManager::WIDGET_RENDER_TYPE_TABLE,
                DashboardWidgetManager::WIDGET_RENDER_TYPE_STAT,
                DashboardWidgetManager::WIDGET_RENDER_TYPE_GAUGE,
                DashboardWidgetManager::WIDGET_RENDER_TYPE_BUBBLE,
            ],
            'multiple'          => true,
            'choices_as_values' => true,
            'required'          => true,
        ]);

        if (($builder->getData() && $builder->getData()->isCustom()) || $options['display_only']) {
            $builder
                ->add('title', TextType::class, [
                    'required' => true,
                ])
                ->add('description', TextType::class, [
                    'required' => false,
                ])
                ->add('labels', CollectionType::class, [
                    'entry_type'     => TextType::class,
                    'allow_add'      => true,
                    'allow_delete'   => true,
                    'error_bubbling' => false,
                    'required'       => false,
                ])
                ->add('variables', CollectionType::class, [
                    'entry_type'     => ReportWidgetVariableType::class,
                    'allow_add'      => true,
                    'allow_delete'   => true,
                    'error_bubbling' => false,
                    'required'       => false,
                ])
                ->add('input_mode', ChoiceType::class, [
                    'required'          => false,
                    'mapped'            => false,
                    'choices_as_values' => true,
                    'choices'           => [
                        'dpql',
                        'form',
                    ],
                ])
            ;
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (isset($data['labels']) && is_array($data['labels'])) {
            $language = $this->languageManager->getLanguageStack()->getActive();
            if (!$language) {
                $language = $this->languageManager->getLanguageStack()->getDefaultLanguage();
            }

            $labelPhrases = $this->phraseLoader->load(['reports.labels'], $language);
            $phrasesMap   = [];
            foreach ($labelPhrases as $key => $phrase) {
                $phrasesMap[$phrase] = $key;
            }

            foreach ($data['labels'] as &$label) {
                if (isset($phrasesMap[$label])) {
                    $label = preg_replace('/\.(.*?)$/', '$1', strtolower($label));
                }
            }
        } else {
            $form->remove('labels');
        }

        if ($form->has('input_mode')) {
            if (isset($data['input_mode']) && $data['input_mode'] === 'dpql') {
                $form->add('query', TextareaType::class, [
                    'required' => true,
                ]);
            } else {
                $form->add('query_parts', DpqlPartsType::class, [
                    'property_path'  => 'query',
                    'required'       => true,
                    'error_bubbling' => false,
                    'variables'      => isset($data['variables']) ? $data['variables'] : [],
                ]);
            }
        }

        $event->setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'   => ReportWidget::class,
            'display_only' => false,
        ]);
    }
}
