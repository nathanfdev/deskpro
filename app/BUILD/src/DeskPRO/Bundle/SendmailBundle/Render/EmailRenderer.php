<?php

namespace DeskPRO\Bundle\SendmailBundle\Render;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Blob;
use Application\EmailBundle\Templating\Templates\EmailTemplateCode;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Parser\JmsMetadataParser;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use JMS\Serializer\Serializer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class EmailRenderer.
 */
class EmailRenderer
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var EngineInterface
     */
    private $templateEngine;

    /**
     * @var DeskproContainer|Container
     */
    private $serviceContainer;

    /**
     * EmailRenderer constructor.
     *
     * @param Serializer      $serializer
     * @param EngineInterface $templateEngine
     * @param Container       $serviceContainer
     */
    public function __construct(Serializer $serializer, EngineInterface $templateEngine, Container $serviceContainer)
    {
        $this->serializer       = $serializer;
        $this->templateEngine   = $templateEngine;
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param Serializer $serializer
     *
     * @return EmailRenderer
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * @return EngineInterface
     */
    public function getTemplateEngine()
    {
        return $this->templateEngine;
    }

    /**
     * @param EngineInterface $templateEngine
     *
     * @return EmailRenderer
     */
    public function setTemplateEngine($templateEngine)
    {
        $this->templateEngine = $templateEngine;

        return $this;
    }

    /**
     * @param string        $templateName
     * @param EmailBaseType $model
     *
     * @return EmailTemplateCode
     */
    public function render($templateName, EmailBaseType $model)
    {
        $context = new SideloadSerializationContext();
        $context->setIncludesStrategy(SideloadSerializationContext::INCLUDE_STRATEGY_DATA);
        $context->setInlineSideloads(true);

        // wrap to make sideloading works
        $model = new ApiWrapper($model);

        $vars = $this->getSerializer()->toArray($model, $context)['data'];
        $code = $this->getTemplateEngine()->render($templateName, $vars);

        $blobAuthIds = [];

        // We look for <attachement id='{id}'> and remove it from the template
        $code = preg_replace_callback('#<attachment[^>]*id=("([^"]+)"|\'([^\']+)\')[^>]*>#',
            function ($matches) use (&$blobAuthIds) {
                $blobAuthIds[] = $matches[2] ? $matches[2] : $matches[3];

                return '';
            },
            $code
        );

        $blobSysNames = [];

        // We look for <attachement sys='{id}'> and remove it from the template
        $code = preg_replace_callback('#<attachment[^>]*sys=("([^"]+)"|\'([^\']+)\')[^>]*>#',
            function ($matches) use (&$blobSysNames) {
                $blobSysNames[] = $matches[2] ? $matches[2] : $matches[3];

                return '';
            },
            $code
        );

        $templateCode = new EmailTemplateCode($code);

        foreach ($blobAuthIds as $authId) {
            /** @var Blob $blob */
            $blob = $this->serviceContainer->getEm()->getRepository(Blob::class)->getByAuthId($authId);
            if ($blob) {
                $templateCode->addAttachment($blob);
            }
        }

        foreach ($blobSysNames as $sysName) {
            /** @var Blob $blob */
            $blob = $this->serviceContainer->getEm()->getRepository(Blob::class)->getSystemBlob($sysName);
            if ($blob) {
                $templateCode->addAttachment($blob);
            }
        }

        return $templateCode;
    }

    /**
     * @param EmailBaseType|string $model
     *
     * @return array
     */
    public function getStructure($model)
    {
        /** @var JmsMetadataParser $parser */
        $parser = $this->serviceContainer->get('dp_api_doc.parser.jms_metadata_parser');
        $class  = is_object($model) ? get_class($model) : $model;

        return $this->simplifyStructure($parser->parse(['class' => $class, 'groups' => []]));
    }

    private function simplifyStructure($parsedModel, $level = 0)
    {
        $structure = [];
        foreach ($parsedModel as $key => $attribute) {
            if ($attribute['subType'] && $level) {
                continue;
            }
            $structure[$key] = [
                'description' => $attribute['description'],
                'type'        => $attribute['dataType'],
                'attribute'   => $key,
            ];
            if (!empty($attribute['children'])) {
                $structure[$key]['properties'] = $this->simplifyStructure($attribute['children'], $level + 1);
            }
        }

        return $structure;
    }
}
