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
namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\LabelOrganization;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class OrganizationType.
 */
class OrganizationType extends ApiType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('parent', 'entity', [
                'class' => 'DeskPRO:Organization',
            ])
            ->add('picture_blob', 'auth_blob', [
                'required'      => false,
                'property_path' => 'picture_blob',
            ])
            ->add('summary', 'text')
            ->add('importance', 'integer')
            ->add('labels', 'api_labels_collection', [
                'labels_class'   => LabelOrganization::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'organization',
            ])
        ;
    }
}
