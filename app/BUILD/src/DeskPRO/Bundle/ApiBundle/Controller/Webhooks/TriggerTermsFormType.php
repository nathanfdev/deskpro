<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Webhooks;

use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TriggerTermsFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['allow_extra_fields' => true]);
    }

    public function onSubmit(FormEvent $event)
    {
        $form      = $event->getForm();
        $extraData = $form->getExtraData();

        $triggerTerms = new TriggerTerms();
        foreach ($extraData as $conjunction) {
            if (is_array($conjunction)) {
                $composite = new TriggerTermComposite([], TriggerTermComposite::OP_AND);
                foreach ($conjunction as $term) {
                    if (is_array($term)) {
                        $termObject = $triggerTerms->getTermFromArray($term);
                        $composite->add($termObject);
                    }
                }
                if ($composite->count()) {
                    $triggerTerms->addTerm($composite);
                }
            }
        }

        $event->setData($triggerTerms);
    }
}
