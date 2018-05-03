<?php

namespace DeskPRO\Bundle\ImportBundle\Validator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\ValidatorBuilder;

/**
 * Class ImporterValidatorFactory.
 */
class ImporterValidatorFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return \Symfony\Component\Validator\ValidatorInterface
     */
    public static function getValidator(ContainerInterface $container)
    {
        $validatorBuilder = $container->get('validator.builder');

        // check if annotations are enabled
        $reflection = new \ReflectionProperty(ValidatorBuilder::class, 'annotationReader');
        $reflection->setAccessible(true);

        // force enable annotations for the importer
        if (!$reflection->getValue($validatorBuilder)) {
            try {
                $validatorBuilder->enableAnnotationMapping($container->get('annotation_reader'));

                return $validatorBuilder->getValidator();
            } finally {
                $validatorBuilder->disableAnnotationMapping();
            }
        } else {
            return $validatorBuilder->getValidator();
        }
    }
}
