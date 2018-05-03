<?php

namespace DeskPRO\Bundle\ApiBundle\Request\ParamConverter;

use DeskPRO\Bundle\AppBundle\Features\FeaturesCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class FeatureConverter.
 */
class FeatureConverter implements ParamConverterInterface
{
    /**
     * @var FeaturesCollection
     */
    private $featuresCollection;

    /**
     * Constructor.
     *
     * @param FeaturesCollection $featuresCollection
     */
    public function __construct(FeaturesCollection $featuresCollection)
    {
        $this->featuresCollection = $featuresCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $id      = $request->attributes->get($configuration->getName());
        $feature = $this->featuresCollection->getFeature($id);

        if (!$feature) {
            throw new NotFoundHttpException("Feature `$id` not found.");
        }

        $request->attributes->set($configuration->getName(), $feature);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'feature';
    }
}
