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


use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\People\PersonGuest;
use Application\FormBundle\Captcha\CaptchaDecider;
use Application\FormBundle\Validator\Constraints\ValidCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;

class NewFeedbackType extends AbstractType
{
    /**
     * @var CaptchaDecider
     */
    private $captcha_decider;

    public function __construct(CaptchaDecider $captcha_decider)
    {
        $this->captcha_decider = $captcha_decider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text');
        $builder->add('content', 'textarea');
        $builder->add('category', 'feedback_category', array(
            'person' => $options['person']
        ));

        $builder->add('attachments', 'feedback_attachment_collection', array(
            'person' => $options['person']
        ));
        $builder->add('more_attachments', 'submit', array(
            'validation_groups' => false,
            'label' => 'Add Another Attachment'
        ));

        if (!$options['person'] || $options['person'] instanceof PersonGuest) {
            $builder->add('name', 'text', array(
                'constraints' => new Length(array('min' => 2)),
                'property_path' => 'person.name'
            ));
            $builder->add('email', 'deskpro_person_email', array(
                'label' => false,
                'property_path' => 'person.primary_email',
                'email_label' => false
            ));
        }

        if ($this->captcha_decider->shouldRequireContentCaptchaForCurrentUser()) {
            $builder->add('captcha', 'deskpro_captcha', array(
                'mapped' => false,
                'error_bubbling' => false,
                'constraints' => array(
                    new ValidCaptcha()
                )
            ));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(
                array(
                    'person'
                )

            )
            ->setAllowedTypes(
                array(
                    'person' => 'Application\DeskPRO\Entity\Person'
                )
            )
            ->setDefaults(
                array(
                    'data_class' => 'Application\DeskPRO\Entity\Feedback'
                )
            )
        ;
    }

    public function getName()
    {
        return 'new_feedback';
    }
}
