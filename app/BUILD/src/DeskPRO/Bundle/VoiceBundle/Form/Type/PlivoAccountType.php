<?php

namespace DeskPRO\Bundle\VoiceBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount;
use DeskPRO\Bundle\VoiceBundle\Plivo\PlivoAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PlivoAccountType.
 */
class PlivoAccountType extends AbstractType
{
    /**
     * @var PlivoAdapter
     */
    private $plivoAdapter;

    /**
     * Constructor.
     *
     * @param PlivoAdapter $plivoAdapter
     */
    public function __construct(PlivoAdapter $plivoAdapter)
    {
        $this->plivoAdapter = $plivoAdapter;
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
            'data_class' => PlivoVoiceAccount::class,
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
        if (!$account instanceof PlivoVoiceAccount) {
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

        $properties = $this->plivoAdapter->getAccount($account);
        if ($properties) {
            $account->setAccountName($properties->name);
        }
    }
}
