<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Phrase;

use DeskPRO\Bundle\AppBundle\Entity\PhraseTranslatableInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormEvent;

/**
 * Class PhraseContext.
 */
class PhraseContext
{
    /**
     * @var FormConfigInterface
     */
    private $config;

    /**
     * Constructor.
     *
     * @param FormEvent $event
     */
    public function __construct(FormEvent $event)
    {
        $this->config = $event->getForm()->getConfig();
    }

    /**
     * @return PhraseTranslatableInterface
     */
    public function getOwner()
    {
        return $this->config->getOption('owner');
    }

    /**
     * @return string
     */
    public function getPropName()
    {
        return $this->config->getOption('prop_name');
    }

    /**
     * @return string
     */
    public function getPhraseName()
    {
        return $this->getOwner()->getPhraseName($this->getPropName());
    }
}
