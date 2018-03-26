<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\ObjectLang;

use DeskPRO\Bundle\AppBundle\Entity\ObjectTranslatableInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormEvent;

/**
 * Class ObjectLangContext.
 */
class ObjectLangContext
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
     * @return ObjectTranslatableInterface
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
}
