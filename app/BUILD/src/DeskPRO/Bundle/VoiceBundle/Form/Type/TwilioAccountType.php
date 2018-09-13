<?php

namespace DeskPRO\Bundle\VoiceBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\VoiceBundle\Twilio\TwilioAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TwilioAccountType.
 */
class TwilioAccountType extends AbstractType
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
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 200);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return VoiceAccountType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TwilioVoiceAccount::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $account = $event->getData();
        if (!$account instanceof TwilioVoiceAccount) {
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
        if (!$account->getAccountId() || !$account->getAuthToken()) {
            return;
        }

        $properties = $this->twilioAdapter->getAccount($account);
        if ($properties) {
            $account->setAccountName($properties->friendlyName);
        }
    }
}
