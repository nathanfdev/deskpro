<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\EventListener;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\AgentClientInfoSettings;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\Asset\Packages;

/**
 * Class AgentClientInfoSettingsListener.
 */
class AgentClientInfoSettingsListener implements EventSubscriberInterface
{
    /**
     * @var AppEnvInterface
     */
    private $appEnv;

    /**
     * @var Packages
     */
    private $assetPackages;

    /**
     * Constructor.
     *
     * @param AppEnvInterface $appEnv
     * @param Packages        $assetPackages
     */
    public function __construct(AppEnvInterface $appEnv, Packages $assetPackages)
    {
        $this->appEnv        = $appEnv;
        $this->assetPackages = $assetPackages;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event'  => Events::PRE_SERIALIZE,
                'method' => 'onAddLinked',
                'class'  => AgentClientInfoSettings::class,
            ],
        ];
    }

    /**
     * @internal
     *
     * @param ObjectEvent $event
     */
    public function onAddLinked(ObjectEvent $event)
    {
        $context = $event->getContext();
        if (!$context instanceof SideloadSerializationContext) {
            return;
        }

        $sideloads = $context->getSideloadStore();

        if ($context->hasInclude('message_css')) {
            $sideloads->addCustomSideload('message_css', 0, new CallbackDeferredProperty([$this, 'getMessageCss']));
        }
    }

    /**
     * @internal
     *
     * @return string|null
     */
    public function getMessageCss()
    {
        $assetDir = $this->appEnv->getAppWwwAssetDir();
        $cssPath  = $assetDir.'/pub/build/api_message_style.css';

        return file_exists($cssPath) ? file_get_contents($cssPath) : null;
    }
}
