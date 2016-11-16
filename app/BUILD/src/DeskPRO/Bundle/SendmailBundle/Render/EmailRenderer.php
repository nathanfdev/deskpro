<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\SendmailBundle\Render;

use Application\EmailBundle\Templating\Templates\EmailTemplateCode;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Parser\JmsMetadataParser;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use JMS\Serializer\Serializer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Templating\EngineInterface;

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
     * @var Container
     */
    private $serviceContainer;

    public function __construct(Serializer $serializer, EngineInterface $engine, Container $serviceContainer)
    {
        $this->setSerializer($serializer);
        $this->setTemplateEngine($engine);
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
        $vars = $this->getSerializer()->toArray($model, new SideloadSerializationContext());

        return new EmailTemplateCode($this->getTemplateEngine()->render($templateName, $vars));
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

    private function simplifyStructure($parsedModel)
    {
        $structure = [];
        foreach ($parsedModel as $key => $attribute) {
            $structure[$key] = [
                'description' => $attribute['description'],
                'type'        => $attribute['dataType'],
                'attribute'   => $key,
            ];
            if (!empty($attribute['children'])) {
                $structure[$key]['properties'] = $this->simplifyStructure($attribute['children']);
            }
        }

        return $structure;
    }
}
