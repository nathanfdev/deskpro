<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Language as LanguageModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;

/**
 * Class LanguageHandler.
 */
class LanguageHandler extends AbstractEntityHandler
{
    /**
     * @var BrandAwareSettingsResolver
     */
    private $settingsResolver;

    /**
     * @var AssetsHelper
     */
    private $assetsHelper;

    /**
     * Constructor.
     *
     * @param BrandAwareSettingsResolver $settingsResolver
     * @param AssetsHelper               $assetsHelper
     */
    public function __construct(BrandAwareSettingsResolver $settingsResolver, AssetsHelper $assetsHelper)
    {
        $this->settingsResolver = $settingsResolver;
        $this->assetsHelper     = $assetsHelper;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return Language::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Language $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $baseUrl   = $helpdeskUrl   = rtrim($this->settingsResolver->getSetting('core.deskpro_url'), '/');
        $flagImage = $baseUrl.$this->assetsHelper->getUrl('images/flags/'.$entity->getFlagImage(), 'legacy_web');

        return new LanguageModel($entity, $flagImage);
    }
}
