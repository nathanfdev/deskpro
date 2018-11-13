<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Type;

use Application\DeskPRO\App;
use Application\DeskPRO\Form\Type\PhoneNumberType;
use Application\DeskPRO\Translate\Translate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SettingsProfile extends AbstractType
{
    /**
     * @var Translate
     */
    protected $translate;

    /**
     * @param Translate $translate
     */
    public function __construct(Translate $translate)
    {
        $this->translate = $translate;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['required' => false]);
        $builder->add('primary_phone', new PhoneNumberType());
        $builder->add('override_display_name', 'text', ['required' => false]);
        $builder->add('email', 'text', ['required' => false]);
        $builder->add('timezone', 'choice', [
            'choices' => array_combine(\DateTimeZone::listIdentifiers(), \DateTimeZone::listIdentifiers()),
        ]);

        $lang_names = [];
        foreach (App::getContainer()->getLanguageData()->getAll() as $lang) {
            if ($lang->has_agent) {
                $lang_names[$lang->id] = $this->translate->getPhraseObject($lang, 'title');
            }
        }

        $builder->add('language_id', 'choice', [
            'choices' => $lang_names,
        ]);
        $builder->add('password', 'password', ['required' => false]);
        $builder->add('password2', 'password', ['required' => false]);

        $builder->add('ticket_close_reply', 'checkbox', ['required' => false]);
        $builder->add('ticket_close_note', 'checkbox', ['required' => false]);
        $builder->add('hide_claimed_chat', 'checkbox', ['required' => false]);
        $builder->add('ticket_go_next_reply', 'checkbox', ['required' => false]);
        $builder->add('ticket_reverse_order', 'checkbox', ['required' => false]);
        $builder->add('enable_plaintext_email', 'checkbox', ['required' => false]);

        $builder->add('reset_api_token', 'hidden', ['required' => false]);

        $builder->add('primary_team_id', 'hidden', ['required' => false]);

        $builder->add('new_picture_blob_id', 'hidden', ['required' => false]);
        $builder->add('remove_picture', 'checkbox', ['required' => false]);

        $builder->add('auto_dismiss_notifications', 'choice', [
            'choices' => [
                5    => $this->translate->phrase('agent.time.x_second', ['count' => 5]),
                10   => $this->translate->phrase('agent.time.x_second', ['count' => 10]),
                15   => $this->translate->phrase('agent.time.x_second', ['count' => 15]),
                30   => $this->translate->phrase('agent.time.x_second', ['count' => 30]),
                60   => $this->translate->phrase('agent.time.x_minute', ['count' => 1]),
                120  => $this->translate->phrase('agent.time.x_minute', ['count' => 2]),
                300  => $this->translate->phrase('agent.time.x_minute', ['count' => 5]),
                900  => $this->translate->phrase('agent.time.x_minute', ['count' => 15]),
                1800 => $this->translate->phrase('agent.time.x_minute', ['count' => 30]),
                3600 => $this->translate->phrase('agent.time.x_hour', ['count' => 1]),
                0    => $this->translate->phrase('agent.general.never'),
            ],
            'expanded' => false,
            'multiple' => false,
        ]);
    }

    public function getDefaultOptions(array $options)
    {
        return [
            'data_class' => 'Application\\AgentBundle\\Form\\Model\\SettingsProfile',
        ];
    }

    public function getName()
    {
        return 'settings_profile';
    }
}
