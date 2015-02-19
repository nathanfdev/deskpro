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
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\BlobStorage;
use Application\FormBundle\Form\DataTransformer\BlobTypeModelTransformer;
use Application\FormBundle\Form\DataTransformer\BlobTypeViewTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BlobType extends AbstractType
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var DeskproBlobStorage
     */
    private $blob_storage;

    public function __construct(EntityManager $em, DeskproBlobStorage $blob_storage)
    {
        $this->em = $em;
        $this->blob_storage = $blob_storage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreData'));
        $builder->addEventListener(FormEvents::SUBMIT, array($this, 'onPostSubmit'));

        $builder->addModelTransformer(new BlobTypeModelTransformer($this->em->getRepository('DeskPRO:Blob')));
    }

    public function onPostSubmit(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Blob $blob */
        $blob = $event->getData();

        $form = $event->getForm();

        /** @var \Application\DeskPRO\Entity\TicketAttachment $attachment */
        $blob = $event->getData() instanceof Blob ? $event->getData() : new Blob();
        $form = $event->getForm();

        if ($form->has('upload')) {
            $file = $form->get('upload')->getData();

            if ($file instanceof File && $file->getRealPath()) {
                $blob = $this->blob_storage->createBlobRecordFromFile(
                    $file->getRealPath(),
                    $file->getClientOriginalName(),
                    $file->getClientMimeType()
                );

                $this->em->persist($blob);
                $form->setData($blob);
                $form->remove('upload');
                $form->add('delete', 'checkbox', array('mapped' => false, 'required' => false));
                $form->add('blob_auth', 'hidden');
            }
        } else {
            if (!$form->has('blob_auth')) {
                $form->add('blob_auth', 'hidden');
            }
        }
    }

    public function onPreData(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Blob $blob */
        $blob = $event->getData();

        $form = $event->getForm();

        if (!$blob) {
            $form->add('upload', 'file', array(
                'mapped' => false
            ));

            if ($form->has('delete_blob')) {
                $form->remove('delete_blob');
            }

            if ($form->has('blob_auth')) {
                $form->remove('blob_auth');
            }

            return;
        } else {
            $form->add('delete_blob', 'checkbox');
            $form->add('blob_auth', 'hidden');

            if ($form->has('upload')) {
                $form->remove('upload');
            }
        }

    }

    public function getName()
    {
        return 'deskpro_blob';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => null
            )
        );
    }
}
 