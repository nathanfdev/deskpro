<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class VoiceAssetHelper.
 */
class VoiceAssetHelper
{
    /**
     * @var StorageAdapterInterface
     */
    private $taskStorage;

    /**
     * @var VoiceTaskHelper
     */
    private $taskHelper;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Packages
     */
    private $assetPackages;

    /**
     * Constructor.
     *
     * @param StorageAdapterInterface $taskStorage
     * @param VoiceTaskHelper         $taskHelper
     * @param RouterInterface         $router
     * @param Packages                $assetPackages
     */
    public function __construct(
        StorageAdapterInterface $taskStorage,
        VoiceTaskHelper         $taskHelper,
        RouterInterface         $router,
        Packages                $assetPackages
    ) {
        $this->taskStorage   = $taskStorage;
        $this->taskHelper    = $taskHelper;
        $this->router        = $router;
        $this->assetPackages = $assetPackages;
    }

    /**
     * @return string
     */
    public function getDefaultRingAssetUrl()
    {
        $baseUrl = $this->router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $baseUrl = rtrim($baseUrl, '/');

        $asset = $this->assetPackages->getUrl('DeskPRO/Bundle/AgentBundle/Resources/sounds/outgoing-ringtone.mp3', 'app_assets');
        if (preg_match('#^http://localhost:9666/#', $asset)) {
            $asset = preg_replace('#^http://localhost:9666/#', $baseUrl.'/assets/BUILD/', $asset);
        } elseif (preg_match('#^/assets/#', $asset)) {
            $asset = $baseUrl.$asset;
        }

        return $asset;
    }

    /**
     * @param string $taskId
     *
     * @return AbstractVoiceAsset|null
     */
    public function getVoicemailAsset($taskId)
    {
        if (!$taskId) {
            return;
        }

        $task = $this->taskStorage->getTask($taskId);
        if ($task) {
            $queue = $this->taskHelper->getVoiceQueue($task);
            $agent = $this->taskHelper->getWorkerAgent($task);

            $asset = null;
            if ($queue) {
                // get custom queue voicemail asset
                $asset = $queue->getVoicemailAsset();
            } elseif ($agent) {
                // get custom agent voicemail asset
                $agentData = $agent->getAgentData();
                if ($agentData) {
                    $asset = $agentData->getVoicemailAsset();
                }
            }

            return $asset;
        }

        return;
    }
}
