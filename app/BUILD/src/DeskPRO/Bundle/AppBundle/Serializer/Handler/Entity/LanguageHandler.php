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
