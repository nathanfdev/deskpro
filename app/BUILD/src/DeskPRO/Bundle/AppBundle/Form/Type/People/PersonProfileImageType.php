<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\AppBundle\Form\Type\BlobAuthType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WebTicketMessageAttachmentCollectionType.
 */
class PersonProfileImageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BlobAuthType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Blob::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'person_profile_image';
    }
}
