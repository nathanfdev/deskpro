<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\DpCaptchaType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\Community\CommunityTopicAttachmentCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonEmailType;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
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
 * Class NewCommunityTopicType.
 */
class NewCommunityTopicType extends AbstractType
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
     * @var PortalBrandThemeLoader
     */
    private $portalBrandThemeLoader;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param CaptchaDecider $captchaDecider
     * @param LanguageManager $languageManager
     * @param CustomFieldManager $fieldManager
     * @param HierarchyGenerator $hierarchyGenerator
     * @param PortalBrandThemeLoader|null $portalBrandThemeLoader
     * @param BrandStack|null $brandStack
     */
    public function __construct(
        CaptchaDecider $captchaDecider,
        LanguageManager $languageManager,
        CustomFieldManager $fieldManager,
        HierarchyGenerator $hierarchyGenerator,
        PortalBrandThemeLoader $portalBrandThemeLoader = null,
        BrandStack $brandStack = null
    ) {
        $this->captchaDecider          = $captchaDecider;
        $this->languageManager         = $languageManager;
        $this->fieldManager            = $fieldManager;
        $this->hierarchyGenerator      = $hierarchyGenerator;
        $this->portalBrandThemeLoader  = $portalBrandThemeLoader;
        $this->brandStack              = $brandStack;
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

        if ($options['has_forum_selection']) {
            if ($this->hierarchyGenerator->generateForCommunityForums($options['person'])->countSelectable() > 0) {
                $builder->add('forum', CommunityForumType::class, [
                    'person'      => $options['person'],
                    'empty_value' => $this->phrase('portal.forms.label_select'),
                    'constraints' => [
                        new NotNull(),
                    ],
                ]);
            }
        }

        $builder
            ->add('custom_data', CombinedType::class, [
                'forms'        => $this->getCustomDataForms(),
                'fields_group' => true,
            ])
            ->add('attachments', CommunityTopicAttachmentCollectionType::class, [
                'person' => $options['person'],
                'topic'  => $builder->getData(),
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
            if ($this->captchaDecider->shouldRequireCommunityCaptchaForCurrentPerson()) {
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
            ->setDefaults([
                'data_class'          => CommunityTopic::class,
                'has_forum_selection' => true,
            ])
            ->setAllowedTypes('person', Person::class)
            ->setAllowedTypes('has_forum_selection', ['bool'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'new_community_topic';
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
        $defs  = $this->fieldManager->getAvailableCommunityDefs();

        foreach ($defs as $def) {
            if (!$def->getTitle()) {
                switch ($def->sys_name) {
                    case 'chan':
                        $def->setTitle($this->phrase(["portal.community.form_custom_cat", "helpcenter.community.channel"]));

                        break;
                    default:
                        throw new \Exception('missing declaration for community sys title');
                }
            }

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
     * @return bool
     */
    public function isHelpCenterTheme()
    {
        return $this->portalBrandThemeLoader->getPortalBrandTheme($this->brandStack->getActive()->getBrand())->getActiveThemeSet()->getThemeId() === 'helpcenter';
    }

    /**
     * @param string|array $name
     * @param array  $vars
     *
     * @return string
     */
    protected function phrase($name, array $vars = [])
    {
        if (is_array($name)) {
            if ($this->isHelpCenterTheme()) {
                $name = array_filter($name, function ($p) {
                    return strpos($p, 'helpcenter.') === 0;
                });
            } else {
                $name = array_filter($name, function ($p) {
                    return strpos($p, 'helpcenter.') !== 0;
                });
            }

            $name = array_pop($name);
        }

        return $this->languageManager->phrase($name, $vars);
    }
}
