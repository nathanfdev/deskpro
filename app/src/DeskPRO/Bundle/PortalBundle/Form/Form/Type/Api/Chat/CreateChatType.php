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
namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\TextStringTransformer;
use Orb\Util\Strings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CreateChatType.
 */
class CreateChatType extends AbstractType
{
    /**
     * @var SetPersonListener
     */
    private $set_person_listener;

    /**
     * @var SettingsResolver
     */
    private $settings_resolver;

    /**
     * Constructor.
     *
     * @param SetPersonListener $set_person_listener
     * @param SettingsResolver  $settings_resolver
     */
    public function __construct(SetPersonListener $set_person_listener, SettingsResolver $settings_resolver)
    {
        $this->set_person_listener = $set_person_listener;
        $this->settings_resolver   = $settings_resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'api_chat_create';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $email_constraints = [new Assert\Email()];
        if ($this->getGlobalSettings()->get('portal.chat.email_validation')) {
            $email_constraints[] = new Assert\NotBlank();
        }

        $builder
            ->add('name', 'text', [
                'property_path' => 'person_name',
                'required'      => false,
            ])
            ->add('email', 'email', [
                'property_path' => 'person_email',
                'required'      => false,
                'constraints'   => $email_constraints,
            ])
        ;

        $builder->get('name')->addModelTransformer(new TextStringTransformer());
        $builder->get('email')->addModelTransformer(new TextStringTransformer());

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this->set_person_listener, 'onSetPerson']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetEmailValidationCode']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection'               => false,
            'csrf_double_submit_protection' => false,
        ]);
    }

    /**
     * @param FormEvent $event
     */
    public function onSetEmailValidationCode(FormEvent $event)
    {
        if (!$this->getGlobalSettings()->get('portal.chat.email_validation')) {
            return;
        }

        $event->getForm()->add('email_validation_code', 'text');
        $event->setData(array_merge($event->getData(), [
            'email_validation_code' => Strings::random(15, Strings::CHARS_KEY),
        ]));
    }

    /**
     * @return \Application\DeskPRO\NewSettings\SettingsBag
     */
    protected function getGlobalSettings()
    {
        return $this->settings_resolver->getGlobalSettings();
    }
}
