<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\ApiToken;
use DeskPRO\Bundle\AppBundle\Serializer\Model\ApiToken as ApiTokenModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Settings\DiscoverSettingsResolver;

/**
 * Class ApiTokenHandler.
 */
class ApiTokenHandler extends AbstractEntityHandler
{
    /**
     * @var DiscoverSettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param DiscoverSettingsResolver $settingsResolver
     */
    public function __construct(DiscoverSettingsResolver $settingsResolver)
    {
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return ApiToken::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param ApiToken $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new ApiTokenModel($entity, $this->settingsResolver->getSettings());
    }
}
