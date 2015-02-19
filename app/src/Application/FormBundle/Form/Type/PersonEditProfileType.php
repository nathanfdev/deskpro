<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Form\Type;


use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\ORM\EntityManager;
use Application\FormBundle\Form\FormFieldManager;
use Application\LanguageBundle\Language\LanguageManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PersonEditProfileType extends AbstractType
{
    /**
     * @var FormFieldManager
     */
    private $field_manager;

    /**
     * @var LanguageManager
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

    public function __construct(FormFieldManager $field_manager, LanguageManager $language_manager, DeskproBlobStorage $blob_storage, EntityManager $em)
    {
        $this->field_manager = $field_manager;
        $this->language_manager = $language_manager;
        $this->blob_storage = $blob_storage;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('first_name', 'text');
        $builder->add('last_name', 'text');

        $builder->add('timezone', 'timezone', array());

        if ($this->language_manager->isMultiLanguagePortal()) {
            $builder->add('language_id', 'deskpro_language', array(
                'view_context' => 'user'
            ));
        }

        $blob_storage = $this->blob_storage;
        $em = $this->em;
        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use ($blob_storage, $em) {
            /** @var \Application\DeskPRO\Entity\Person $person */
            $person = $event->getData();
            $form = $event->getForm();

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
                    $form->add('delete_picture', 'checkbox', array('required' => false, 'mapped' => false));
                }
            }

            if ($form->has('delete_picture')) {
                if ($form->get('delete_picture')->getData()) {
                    $blob_storage->deleteBlobRecord($person->picture_blob);
                    $person->setPictureBlob(null);
                }
            }
        });


        $field_manager = $this->field_manager;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($field_manager) {

            /** @var \Application\DeskPRO\Entity\Person $person */
            $person = $event->getData();
            $form = $event->getForm();

            if ($person->picture_blob) {
                $form->add('delete_picture', 'checkbox', array('required' => false, 'mapped' => false));
            } else {
                $form->add('upload_picture', 'file', array('required' => false, 'mapped' => false));
            }


            foreach ($field_manager->getAvailablePersonFields() as $field_def) {
                if (!$field_def->is_enabled) {
                    continue;
                }

                $id = $field_def->getId();
                $form->add(
                    $id,
                    'deskpro_custom_data_person',
                    array(
                        'custom_data_field' => $field_def,
                        'person' => $event->getData(),
                        'property_path' => sprintf('getCustomDataCollection[%s]', $id),
                        'agent_interface' => false,
                        'label' => false
                    )
                );
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Application\DeskPRO\Entity\Person'
            )
        );

        $resolver->setRequired(
            array('settings')
        );

        $resolver->setAllowedTypes(
            array(
                'settings' => 'Application\DeskPRO\NewSettings\SettingsBag'
            )
        );
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'person_profile';
    }
}
