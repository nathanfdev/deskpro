<?php

namespace DeskPRO\Bundle\SendmailBundle\Render;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\EmailBundle\Templating\Templates\EmailTemplateCode;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Parser\JmsMetadataParser;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\SendmailBundle\Twig\PreProcessor\EmailPreProcessor;
use DeskPRO\Bundle\SendmailBundle\Twig\TwigEngine;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Templating\EngineInterface;
use Twig_Loader_Chain;

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
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * EmailRenderer constructor.
     *
     * @param Serializer      $serializer
     * @param EngineInterface $templateEngine
     * @param Container       $serviceContainer
     * @param EntityManager   $em
     */
    public function __construct(Serializer $serializer, EngineInterface $templateEngine, Container $serviceContainer, EntityManager $em)
    {
        $this->serializer       = $serializer;
        $this->templateEngine   = $templateEngine;
        $this->serviceContainer = $serviceContainer;
        $this->em               = $em;
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
     * @param string        $code
     * @param string        $tplName
     * @param EmailBaseType $model
     * @param Language      $language
     * @param array         $templates
     *
     * @throws \Exception
     */
    public function renderPreview($code, $tplName, $model, $language, $templates, $string_only = false)
    {
        $recipient = new Person();
        $recipient->setFirstName('FirstName');
        $recipient->setLastName('LastName');
        $email = new PersonEmail();
        $email->setEmail('test@example.com');
        $recipient->setPrimaryEmail($email);
        $recipient->setPassword('Password1234');

        $recipient = $this->serviceContainer->get('api_serializer.handler.person')->createModel($recipient, new SideloadSerializationContext());
        $model->setRecipient($recipient);
        $model->setSiteUrl($this->serviceContainer->getBrandSetting('core.site_url'));
        $model->setSiteName($this->serviceContainer->getBrandSetting('core.site_name'));
        $model->setDeskproUrl($this->serviceContainer->getBrandSetting('core.deskpro_url'));

        $preProcessor = new EmailPreProcessor();
        $code         = $preProcessor->process($code, $tplName);

        $twig = clone $this->serviceContainer->get('templating.new_email.twig');
        $twig->setCache(false);
        $templates[$tplName] = $code;
        $stringLoader        = new \Twig_Loader_Array($templates);
        $hybridLoader        = $this->serviceContainer->get('templating.new_email.twig.loader');
        $loader              = new Twig_Loader_Chain([$stringLoader, $hybridLoader]);
        $twig->setLoader($loader);

        /** @var TwigEngine $twigEngine */
        $twigEngine = $this->serviceContainer->get('templating.new_email.twig.engine');
        $twigEngine->setEnvironment($twig);

        $this->setTemplateEngine($twigEngine);

        $view = null;
        $this->serviceContainer->get('translator')->setTemporaryLanguage(
            $language,
            function () use ($tplName, $model, &$view) {
                $view = $this->render($tplName, $model);
            }
        );

        return $view;
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
