<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Voice;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Twilio\TwilioAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VoiceAccountType.
 */
class VoiceAccountType extends AbstractType
{
    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * Constructor.
     *
     * @param TwilioAdapter $twilioAdapter
     */
    public function __construct(TwilioAdapter $twilioAdapter)
    {
        $this->twilioAdapter = $twilioAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account_name', TextType::class, [
                'property_path' => 'accountName',
                'required'      => true,
            ])
            ->add('account_sid', TextType::class, [
                'property_path' => 'accountSid',
                'required'      => true,
            ])
            ->add('auth_token', TextType::class, [
                'property_path' => 'authToken',
                'required'      => true,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetExternalAccountName'], 200);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VoiceAccount::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetExternalAccountName(FormEvent $event)
    {
        $account = $event->getData();
        if (!$account instanceof VoiceAccount) {
            return;
        }

        // no need to pre-load account name if it's not empty
        if ($account->getAccountName()) {
            return;
        }

        // don't pre-load account name for existing accounts
        if ($account->getId()) {
            return;
        }

        // don't try to pre-load account name w/o credentials
        if (!$account->getAccountSid() || !$account->getAuthToken()) {
            return;
        }

        $properties = $this->twilioAdapter->getAccount($account);
        if ($properties) {
            $account->setAccountName($properties->friendlyName);
        }
    }
}
