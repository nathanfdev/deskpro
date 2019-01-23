<?php

namespace DpSys;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvFactory;
use DeskPRO\Bundle\AppBundle\Features\BetaFeatureInterface;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * This class handles feature flags for both licensed features as well as experimental features.
 */
final class Features
{
    const VOICE = 'voice';
    const DEV   = 'dev';

    /** @var SettingsResolver */
    private $settingsResolver;

    /**
     * @var Features
     */
    private static $inst;

    private function __construct()
    {
    }

    /**
     * @return Features
     */
    public static function getInstance()
    {
        if (!self::$inst) {
            self::$inst = new self();
        }

        return self::$inst;
    }

    /**
     * @internal
     *
     * @param SettingsResolver $settingsResolver
     */
    public function _setSettingsResolver(SettingsResolver $settingsResolver)
    {
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * Do we want to enable an experimental feature?
     *
     * @param string $id The id of the feature (should be a constant above)
     *
     * @return bool
     */
    private function hasExperimental($id)
    {
        return $this->getAppEnv()->getConfig('settings.enable_experimental.all')
            || $this->getAppEnv()->getConfig('settings.enable_experimental.'.$id);
    }

    /**
     * Check if a certain feature is enabeld for the current license / config.
     *
     * @param string $id the id of the feature
     *
     * @return bool
     */
    public function hasFeature($id)
    {
        /*
         * @TODO aftery long discussion with SY and tries to pass here featuresCollection I postponed it.
         * The main purpose to use featuresCollection here - avoid need to change hasBeta to hasFeature in templates
         * see FeaturesListener.
         * Generally it is a good idea, but internal settings of featuresCollection won't work (hasFeature called
         * before it) also we cant pass featuresCollection here via DI cause circular dependency occurs.
         */
        switch ($id) {
            case self::VOICE:
                return $this->hasVoice();
            case self::DEV:
                return $this->getAppEnv()->isQa() || $this->getAppEnv()->isDebug();
            default:
                // assume it's an arbitrary experimental flag
                return $this->hasExperimental($id);
        }
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasBeta($id)
    {
        if (!$this->settingsResolver) {
            $e = new LogicException('There is no settings resolver set in DpSys\Features. Check the code!');
            SystemErrorHandler::logException($e);

            return false;
        }

        $key = sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $id);

        return $this->settingsResolver->getGlobalSettings()->getBool($key, false);
    }

    /**
     * Can we use voice? Voice is only enabled if the license has voice or if we're in dev mode.
     *
     * @return bool
     */
    public function hasVoice()
    {
        return (
            ($this->getLicense()->hasFlag('has_voice') || $this->getLicense()->hasFlag('is_dev'))
            || $this->getLicense()->isCloud()
        ) && $this->hasBeta('voice');
    }

    /**
     * @return License
     */
    private function getLicense()
    {
        return License::getLicense();
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface
     */
    private function getAppEnv()
    {
        return AppEnvFactory::create();
    }
}
