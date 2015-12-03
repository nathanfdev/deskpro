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

use DeskPRO\Bundle\AppBundle\Validator\Constraints\NotBannedEmail;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class PersonEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email', array(
            'label'       => $options['email_label'],
            'required'    => $options['required'],
            'constraints' => $options['email_constraints'],
        ));
    }

    public function getName()
    {
        return 'deskpro_person_email';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'                 => 'Application\\DeskPRO\\Entity\\PersonEmail',
            'email_label'                => 'Email',
            'email_exists_error_message' => 'This email already exists in the system.',
            'constraints'                => function (Options $options) {
                return array(
                    new UniqueEntity(array('fields' => 'email', 'message' => $options['email_exists_error_message'], 'errorPath' => 'email')),
                );
            },
            'email_constraints' => array(
                new NotBannedEmail(['message' => 'portal.forms.error_banned_email']),
                new NotBlank(array('message'  => 'portal.forms.error_email_required')),
                new Email(array('message'     => 'portal.forms.error_email_invalid')),
            ),
        ));
    }
}
