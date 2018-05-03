<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\DpCaptchaType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\Feedback\FeedbackAttachmentCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonEmailType;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class NewFeedbackType.
 */
class NewFeedbackType extends AbstractType
{
    /**
     * @var CaptchaDecider
     */
    private $captchaDecider;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @var CustomFieldManager
     */
    private $fieldManager;

    /**
     * @var HierarchyGenerator
     */
    private $hierarchyGenerator;

    /**
     * Constructor.
     *
     * @param CaptchaDecider     $captchaDecider
     * @param LanguageManager    $languageManager
     * @param CustomFieldManager $fieldManager
     * @param HierarchyGenerator $hierarchyGenerator
     */
    public function __construct(
        CaptchaDecider     $captchaDecider,
        LanguageManager    $languageManager,
        CustomFieldManager $fieldManager,
        HierarchyGenerator $hierarchyGenerator
    ) {
        $this->captchaDecider     = $captchaDecider;
        $this->languageManager    = $languageManager;
        $this->fieldManager       = $fieldManager;
        $this->hierarchyGenerator = $hierarchyGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label'       => $this->phrase('portal.forms.label_title'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label'       => 'portal.forms.label_content',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;

        if ($this->hierarchyGenerator->generateForFeedbackCategories($options['person'])->countSelectable() > 0) {
            $builder->add('category', FeedbackCategoryType::class, [
                'person'      => $options['person'],
                'empty_value' => $this->phrase('portal.forms.label_select'),
                'constraints' => [
                    new NotNull(),
                ],
            ]);
        }

        $builder
            ->add('custom_data', CombinedType::class, [
                'forms' => $this->getCustomDataForms(),
            ])
            ->add('attachments', FeedbackAttachmentCollectionType::class, [
                'person'   => $options['person'],
                'feedback' => $builder->getData(),
            ])
            ->add('more_attachments', SubmitType::class, [
                'validation_groups' => false,
                'label'             => $this->phrase('portal.forms.label_add_attachment'),
            ])
        ;

        if (!$options['person'] || $options['person'] instanceof PersonGuest) {
            $builder
                ->add('name', TextType::class, [
                    'constraints'   => new Length(['min' => 2]),
                    'property_path' => 'person.name',
                    'label'         => $this->phrase('portal.forms.label_name'),
                ])
                ->add('email', PersonEmailType::class, [
                    'label'         => false,
                    'property_path' => 'person.primary_email',
                    'constraints'   => [], // ignore the "unqiue entity" constraint here
                ])
            ;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if ($this->captchaDecider->shouldRequireFeedbackCaptchaForCurrentPerson()) {
                $event->getForm()->add('captcha', DpCaptchaType::class, [
                    'mapped'         => false,
                    'error_bubbling' => false,
                ]);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setAllowedTypes('person', Person::class)
            ->setDefaults([
                'data_class' => Feedback::class,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'new_feedback';
    }

    /**
     * {@inheritdoc}
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['content']) && is_string($data['content'])) {
            $data['content'] = nl2br(htmlspecialchars($data['content']));
        }

        $event->setData($data);
    }

    /**
     * @return array
     */
    protected function getCustomDataForms()
    {
        $forms = [];
        $defs  = $this->fieldManager->getAvailableFeedbackDefs();

        foreach ($defs as $def) {
            $forms[] = [
                'name'    => $def->getId(),
                'type'    => CustomDataType::class,
                'options' => [
                    'custom_def'      => $def,
                    'property_path'   => 'custom_data',
                    'agent_interface' => false,
                ],
            ];
        }

        return $forms;
    }

    /**
     * @param string $name
     * @param array  $vars
     *
     * @return string
     */
    protected function phrase($name, array $vars = [])
    {
        return $this->languageManager->phrase($name, $vars);
    }
}
