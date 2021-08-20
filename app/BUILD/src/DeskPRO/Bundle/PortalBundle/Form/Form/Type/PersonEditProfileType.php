<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Form\Type\CustomFields\Definitions\ContextualChoiceDefinitionType;
use Application\DeskPRO\NewSettings\SettingsBag;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\People\PersonProfileImageType;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class PersonEditProfileType.
 */
class PersonEditProfileType extends AbstractType
{
    /**
     * @var CustomFieldManager
     */
    private $fieldManager;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Language\LanguageManager
     */
    private $languageManager;

    /**
     * @var DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * Constructor.
     *
     * @param CustomFieldManager $fieldManager
     * @param LanguageManager    $languageManager
     * @param DeskproBlobStorage $blobStorage
     * @param EntityManager      $em
     * @param ValidatorInterface $validator
     */
    public function __construct(
        CustomFieldManager $fieldManager,
        LanguageManager $languageManager,
        DeskproBlobStorage $blobStorage,
        EntityManager $em,
        ValidatorInterface $validator
    ) {
        $this->fieldManager    = $fieldManager;
        $this->languageManager = $languageManager;
        $this->blobStorage     = $blobStorage;
        $this->em              = $em;
        $this->validator       = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $settings = $options['settings'];

        $builder
            ->add('name', TextType::class, [
                'label' => $this->phrase('portal.forms.label_name'),
            ])
            ->add('timezone', TimezoneType::class, [
                'required' => false,
                'label'    => $this->phrase('portal.forms.label_timezone'),
            ])
        ;

        if ($this->languageManager->isMultiLanguagePortal()) {
            $builder->add('language_id', LanguageType::class, [
                'view_context' => 'user',
                'label'        => $this->phrase('portal.forms.label_language'),
            ]);
        }

        if ($settings->get('portal.members_community')) {
            $builder
                ->add('community_name', TextType::class, [
                    'label'    => $this->phrase('portal.forms.label_display_name'),
                    'required' => false,
                ]);
        }

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'onPostSetData']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     *
     * @throws \Exception
     */
    public function onSubmit(FormEvent $event)
    {
        $blobStorage = $this->blobStorage;
        $em          = $this->em;

        /** @var \Application\DeskPRO\Entity\Person $person */
        $person = $event->getData();
        $form   = $event->getForm();

        if ($form->has('upload_picture')) {
            $file = $form->get('upload_picture')->getData();
            if ($file instanceof File && $file->getRealPath()) {
                $blob = $blobStorage->createBlobRecordFromFile(
                    $file->getRealPath(),
                    $file->getClientOriginalName(),
                    $file->getClientMimeType()
                );

                if ($person->getPictureBlob()) {
                    $blobStorage->deleteBlobRecord($person->getPictureBlob());
                }
                $person->setPictureBlob($blob);
                $em->persist($blob);

                $form->remove('upload_picture');
                $form->add('delete_picture', CheckboxType::class, [
                    'required' => false,
                    'mapped'   => false,
                ]);
            } elseif ($file instanceof Blob) {
                if ($person->getPictureBlob()) {
                    $blobStorage->deleteBlobRecord($person->getPictureBlob());
                }
                $person->setPictureBlob($file);
                $file->setIsTemp(false);
                $em->persist($file);

                $form->remove('upload_picture');
                $form->add('delete_picture', CheckboxType::class, [
                    'required' => false,
                    'mapped'   => false,
                ]);
            }
        }

        if ($form->has('delete_picture')) {
            if ($form->get('delete_picture')->getData()) {
                $blobStorage->deleteBlobRecord($person->getPictureBlob());
                $person->setPictureBlob(null);
            }
        }

        if ($form->has('manager_auto_add')) {
            if ($form->get('manager_auto_add')->getData()) {
                $person->setPreference('org.manager_auto_add', 1);
            } else {
                $person->setPreference('org.manager_auto_add', 0);
            }
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSetData(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Person $person */
        $person = $event->getData();
        $form   = $event->getForm();

        if ($form->has('manager_auto_add')) {
            $form->get('manager_auto_add')->setData($person->getPref('org.manager_auto_add') ? true : false);
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Person $person */
        $person = $event->getData();
        $form   = $event->getForm();

        if ($person->getOrganization() && $person->isOrganizationManager()) {
            $form->add('manager_auto_add', SingleCheckboxType::class, [
                'required'       => false,
                'mapped'         => false,
                'label'          => false,
                'checkbox_label' => $this->phrase('portal.account.automatically_join_org_tickets', [
                    'org_name' => $person->getOrganization()->getName(),
                ]),
            ]);
        }

        if ($person->getPictureBlob()) {
            $form->add('delete_picture', SingleCheckboxType::class, [
                'required' => false,
                'mapped'   => false,
            ]);
        } else {
            $form->add('upload_picture', PersonProfileImageType::class, [
                'required' => false,
                'mapped'   => false,
                'label'    => $this->phrase('portal.forms.label_upload_picture'),
            ]);
        }

        foreach ($this->fieldManager->getAvailablePersonDefs() as $def) {
            if (!$def->isEnabled()) {
                continue;
            }
            if ($def->isAgentField()) {
                continue;
            }

            $form->add($def->getId(), CustomDataType::class, [
                'custom_def'      => $def,
                'property_path'   => 'custom_data',
                'agent_interface' => false,
            ]);
        }

        $per_person_defs = $this->fieldManager->getAvailableContextualDefs($person);
        if (!$per_person_defs->count()) {
            return;
        }

        $children = $this->fieldManager->getAvailableContextualDefsChildren($person);
        foreach ($per_person_defs as $def) {
            /* @var $def CustomFieldDefinition */
            $form->add('definition_'.$def->getId(), ContextualChoiceDefinitionType::class, [
                'context'             => $person,
                'data'                => $def,
                'children_collection' => $children,
                'children_only'       => true,
                'label'               => $def['title'],
                'allow_edit'          => isset($def['options']['allow_edit']) ? $def['options']['allow_edit'] : false,
                'mapped'              => false,
            ]);
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if ($data instanceof Person) {
            if ($data->getPictureBlob()) {
                $violations = $this->validator->validate($data->getPictureBlob(), [
                    new AppAssert\BlobRestrictionSet([
                        'imagesOnly' => true,
                    ]),
                ]);

                foreach ($violations as $violation) {
                    $event->getForm()->get('delete_picture')->addError(new FormError(
                        $violation->getMessage(),
                        $violation->getMessageTemplate(),
                        $violation->getParameters(),
                        $violation->getPlural(),
                        $violation
                    ));
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Person::class,
            ])
            ->setRequired('settings')
            ->setAllowedTypes('settings', SettingsBag::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'person_profile';
    }

    /**
     * @param string $name
     * @param array  $vars
     *
     * @return string
     */
    private function phrase($name, array $vars = [])
    {
        return $this->languageManager->phrase($name, $vars);
    }
}
