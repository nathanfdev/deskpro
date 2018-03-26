<?php

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
