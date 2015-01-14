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


use Application\FormBundle\Validator\Constraints\ValidCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content_real', 'textarea', array('label' => 'What is your comment?'));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $comment = $event->getData();
            $form = $event->getForm();

            if ($person = $form->getConfig()->getOption('person')) {
                $comment->setPerson($person);
            } else {
                $form->add('name', 'text', array('label' => 'Your Name'));
                $form->add('email', 'email', array('label' => 'Your Email'));
                $form->add('captcha', 'deskpro_captcha', array(
                    'mapped' => false,
                    'error_bubbling' => false,
                    'constraints' => array(
                        new ValidCaptcha()
                    )
                ));
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\\DeskPRO\\Entity\\CommentAbstract',
            'person' => null
        ));

        $resolver->setRequired(array(
            'person'
        ));

        $resolver->setAllowedTypes(array(
            'person' => array('Application\\DeskPRO\\Entity\\Person', 'null')
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'comment';
    }
}
