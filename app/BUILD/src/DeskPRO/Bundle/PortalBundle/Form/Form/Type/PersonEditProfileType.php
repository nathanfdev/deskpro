<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Form\Type\CustomFields\Definitions\ContextualChoiceDefinitionType;
use Application\DeskPRO\NewSettings\SettingsBag;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * Constructor.
     *
     * @param CustomFieldManager $fieldManager
     * @param LanguageManager    $languageManager
     * @param DeskproBlobStorage $blobStorage
     * @param EntityManager      $em
     */
    public function __construct(CustomFieldManager $fieldManager, LanguageManager $languageManager, DeskproBlobStorage $blobStorage, EntityManager $em)
    {
        $this->fieldManager    = $fieldManager;
        $this->languageManager = $languageManager;
        $this->blobStorage     = $blobStorage;
        $this->em              = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $this->phrase('portal.forms.label_name'),
            ])
            ->add('timezone', TimezoneType::class, [
                'label' => $this->phrase('portal.forms.label_timezone'),
            ])
        ;

        if ($this->languageManager->isMultiLanguagePortal()) {
            $builder->add('language_id', LanguageType::class, [
                'view_context' => 'user',
                'label'        => $this->phrase('portal.forms.label_language'),
            ]);
        }

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'onPostSetData']);
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
        $blob_storage = $this->blobStorage;
        $em           = $this->em;

        /** @var \Application\DeskPRO\Entity\Person $person */
        $person = $event->getData();
        $form   = $event->getForm();

        if ($form->has('upload_picture')) {
            $file = $form->get('upload_picture')->getData();
            if ($file instanceof File && $file->getRealPath()) {
                $blob = $blob_storage->createBlobRecordFromFile(
                    $file->getRealPath(),
                    $file->getClientOriginalName(),
                    $file->getClientMimeType()
                );

                if ($person->getPictureBlob()) {
                    $blob_storage->deleteBlobRecord($person->getPictureBlob());
                }
                $person->setPictureBlob($blob);
                $em->persist($blob);

                $form->remove('upload_picture');
                $form->add('delete_picture', CheckboxType::class, [
                    'required' => false,
                    'mapped'   => false,
                ]);
            }
        }

        if ($form->has('delete_picture')) {
            if ($form->get('delete_picture')->getData()) {
                $blob_storage->deleteBlobRecord($person->getPictureBlob());
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
            $form->add('manager_auto_add', CheckboxType::class, [
                'required'       => false,
                'mapped'         => false,
                'label'          => false,
                'checkbox_label' => $this->phrase('portal.account.automatically_join_org_tickets', [
                    'org_name' => $person->getOrganization()->getName(),
                ]),
            ]);
        }

        if ($person->getPictureBlob()) {
            $form->add('delete_picture', CheckboxType::class, [
                'required' => false,
                'mapped'   => false,
            ]);
        } else {
            $form->add('upload_picture', FileType::class, [
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
