<?php

namespace DeskPRO\Bundle\AppBundle\Twig;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Override JMS serializer extensions because we need to use it with SideloadSerializationContext.
 */
class SerializerExtension extends \JMS\Serializer\Twig\SerializerExtension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param SerializerInterface $serializer
     * @param ContainerInterface  $container
     */
    public function __construct(SerializerInterface $serializer, ContainerInterface $container)
    {
        parent::__construct($serializer);

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('serialize', [$this, 'sideloadSerialize']),
        ];
    }

    /**
     * @param mixed  $value
     * @param array  $includes
     * @param Person $user
     *
     * @return string
     */
    public function sideloadSerialize($value, array $includes = [], Person $user = null)
    {
        $context = new SideloadSerializationContext($includes, $this->container->get('security.token_storage'));
        $context->setUser($user);

        return $this->serializer->serialize(new ApiWrapper($value), 'json', $context);
    }
}
