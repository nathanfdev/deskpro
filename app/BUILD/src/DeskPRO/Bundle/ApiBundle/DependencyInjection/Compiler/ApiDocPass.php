<?php

namespace DeskPRO\Bundle\ApiBundle\DependencyInjection\Compiler;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor\CachingApiDocExtractor;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor\Formatter\HtmlFormatter;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Parser\FormTypeParser;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ApiDocPass.
 */
class ApiDocPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $def = $container->getDefinition('nelmio_api_doc.extractor.api_doc_extractor');
        if (strpos($def->getClass(), 'CachingApiDocExtractor') !== false) {
            $def->setClass(CachingApiDocExtractor::class);
        }

        $def = $container->getDefinition('nelmio_api_doc.parser.form_type_parser');
        $def->setClass(FormTypeParser::class);

        $def = $container->getDefinition('nelmio_api_doc.formatter.html_formatter');
        $def->setClass(HtmlFormatter::class);

        $definition = $container->getDefinition('nelmio_api_doc.extractor.api_doc_extractor');
        $definition->removeMethodCall('addParser');
        $definition->addMethodCall('addParser', [new Reference('dp_api_doc.parser.jms_metadata_parser')]);
    }
}
