<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
