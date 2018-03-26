<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Routing\Generator;

use Application\DeskPRO\App;
use Symfony\Component\Routing\Generator\UrlGenerator as BaseUrlGenerator;
use Symfony\Component\Routing\RequestContext;

/**
 * This URL generator sets a default _locale part with the current Translator locale.
 */
class UrlGenerator extends BaseUrlGenerator
{
    /** @var ObjectUrlGenerator|null */
    protected $object_url_generator = null;

    /**
     * @return \DpRun\DpEnv
     */
    private function getDpEnv()
    {
        return $GLOBALS['DP_ENV'];
    }

    public function setContext(RequestContext $context)
    {
        if (php_sapi_name() === 'cli' && !$this->getDpEnv()->hasRuntimeVar('is_building')) {
            $deskpro_url = rtrim(App::getContainer()->getBrandSetting('core.deskpro_url'), '/');

            if ($deskpro_url) {
                $info = parse_url($deskpro_url);
                $context->setScheme($info['scheme']);
                if (!empty($info['path'])) {
                    $context->setBaseUrl($info['path']);
                } else {
                    $context->setBaseUrl('');
                }
                if (!empty($info['host'])) {
                    $context->setHost($info['host']);
                } else {
                    $context->setHost('');
                }
                $context->setMethod('GET');
                if (!empty($info['port'])) {
                    $context->setHttpPort($info['port']);
                }
            }
        }

        $this->context = $context;
    }

    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        if ($this->getDpEnv()->isDebug()) {
            return parent::generate($name, $parameters, $referenceType);
        }

        // When in prod, eat route not found exceptions because
        // users can mistype them when editing templates
        try {
            return parent::generate($name, $parameters, $referenceType);
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            return;
        }
    }

    public function generateUrl($name, $parameters = [])
    {
        $url = $this->generate($name, $parameters, self::ABSOLUTE_PATH);

        // Make sure index.php is in links
        $deskpro_url = rtrim(App::getContainer()->getBrandSetting('core.deskpro_url'), '/');

        return $deskpro_url.$url;
    }

    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, array $requiredSchemes = [])
    {
        $url = parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes);

        return $url;
    }

    public function getObjectUrlGenerator()
    {
        if ($this->object_url_generator !== null) {
            return $this->object_url_generator;
        }

        $this->object_url_generator = new ObjectUrlGenerator($this);

        return $this->object_url_generator;
    }

    public function generateObjectUrl($object, array $params = [], $context = null)
    {
        return $this->getObjectUrlGenerator()->generateObjectUrl($object, $params, $context);
    }
}
