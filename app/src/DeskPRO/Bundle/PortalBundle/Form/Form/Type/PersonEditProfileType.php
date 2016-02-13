<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class PersonEditProfileType.
 */
class PersonEditProfileType extends AbstractType
{
    /**
     * @var CustomFieldManager
     */
    private $field_manager;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Language\LanguageManager
     */
    private $language_manager;

    /**
     * @var DeskproBlobStorage
     */
    private $blob_storage;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param CustomFieldManager $field_manager
     * @param LanguageManager    $language_manager
     * @param DeskproBlobStorage $blob_storage
     * @param EntityManager      $em
     */
    public function __construct(CustomFieldManager $field_manager, LanguageManager $language_manager, DeskproBlobStorage $blob_storage, EntityManager $em)
    {
        $this->field_manager    = $field_manager;
        $this->language_manager = $language_manager;
        $this->blob_storage     = $blob_storage;
        $this->em               = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'label' => $this->phrase('portal.forms.label_name'),
            ])
            ->add('timezone', 'timezone', [
                'label' => $this->phrase('portal.forms.label_timezone'),
            ])
        ;

        if ($this->language_manager->isMultiLanguagePortal()) {
            $builder->add('language_id', 'deskpro_language', [
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
        $blob_storage = $this->blob_storage;
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

                $person->setPictureBlob($blob);
                $em->persist($blob);

                $form->remove('upload_picture');
                $form->add('delete_picture', 'checkbox', [
                    'required' => false,
                    'mapped'   => false,
                ]);
            }
        }

        if ($form->has('delete_picture')) {
            if ($form->get('delete_picture')->getData()) {
                $blob_storage->deleteBlobRecord($person->picture_blob);
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
        $field_manager = $this->field_manager;

        /** @var \Application\DeskPRO\Entity\Person $person */
        $person = $event->getData();
        $form   = $event->getForm();

        if ($person->organization && $person->organization_manager) {
            $form->add('manager_auto_add', 'checkbox', [
                'required'       => false,
                'mapped'         => false,
                'label'          => false,
                'checkbox_label' => $this->phrase('portal.account.automatically_join_org_tickets', [
                    'org_name' => $person->organization->getName(),
                ]),
            ]);
        }

        if ($person->picture_blob) {
            $form->add('delete_picture', 'checkbox', [
                'required' => false,
                'mapped'   => false,
            ]);
        } else {
            $form->add('upload_picture', 'file', [
                'required' => false,
                'mapped'   => false,
            ]);
        }

        foreach ($field_manager->getAvailablePersonFields() as $field_def) {
            if (!$field_def->isEnabled()) {
                continue;
            }

            $id = $field_def->getId();
            $form->add($id, 'deskpro_custom_data', [
                'custom_def'      => $field_def,
                'owner'           => $event->getData(),
                'agent_interface' => false,
                'label'           => false,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'Application\DeskPRO\Entity\Person',
            ])
            ->setRequired(['settings'])
            ->setAllowedTypes([
                'settings' => 'Application\DeskPRO\NewSettings\SettingsBag',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
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
        return $this->language_manager->phrase($name, $vars);
    }
}
