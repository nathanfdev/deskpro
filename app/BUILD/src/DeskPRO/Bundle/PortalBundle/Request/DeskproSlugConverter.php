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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Request;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\AppBundle\Content\ContentSlugManager;
use DeskPRO\Bundle\PortalBundle\HttpKernel\Exception\PermanentRedirectException;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Used to keep legacy slug urls working (1-slug-here) and forward them to the new (slug-here).
 */
class DeskproSlugConverter implements ParamConverterInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var ContentSlugManager
     */
    private $slugManager;

    public function __construct(EntityManager $em, ContentSlugManager $slugManager)
    {
        $this->em          = $em;
        $this->slugManager = $slugManager;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $paramName         = $configuration->getName();
        $paramClass        = $configuration->getClass();
        $paramOptions      = $this->getOptions($configuration);
        $slugAttributeName = $paramOptions['slug_route_param'];
        $slugInput         = $request->attributes->get($slugAttributeName);
        $slugCol           = $paramOptions['slug_col']; // this will always be "slug" (for now)

        if ($paramName === 'tag_request') {
            return;
        }

        if ($this->isContentClass($paramClass)) {
            if ($obj = $this->slugManager->findContentObjectBySlug($slugInput, $paramClass)) {
                // this must have come from history, we should redirect to the new url
                if ($obj->getSlug() !== $slugInput) {
                    throw new PermanentRedirectException(
                        $request->attributes->get('_route'),
                        array_merge(
                            $request->attributes->get('_route_params'),
                            [
                                $slugAttributeName => $obj->getSlug(),
                            ]
                        )
                    );
                }

                $request->attributes->set($paramName, $obj);

                return;
            }
        }

        // is it in the repo?
        $repo = $this->em->getRepository($paramClass);
        if ($obj = $repo->findOneBy([$slugCol => $slugInput])) {
            $request->attributes->set($paramName, $obj);

            return;
        }

        $id = substr($slugInput, 0, strpos($slugInput, '-'));
        if ($obj = $repo->find($id)) {
            // it exists and we are on the old url at the moment, lets flag a 301 response
            $newSlugAttribute = [
                $slugAttributeName => $obj->slug,
            ];

            throw new PermanentRedirectException(
                $request->attributes->get('_route'),
                array_merge(
                    $request->attributes->get('_route_params'),
                    $newSlugAttribute
                )
            );
        }

        $id = $slugInput;
        if ($obj = $repo->find($id)) {
            // it exists and we are on the old url at the moment, lets flag a 301 response
            $newSlugAttribute = [
                $slugAttributeName => $obj->slug,
            ];

            throw new PermanentRedirectException(
                $request->attributes->get('_route'),
                array_merge(
                    $request->attributes->get('_route_params'),
                    $newSlugAttribute
                )
            );
        }

        throw new NotFoundHttpException(sprintf('could not find a "%s" for the slug value found in the route variable "%s" (value: %s)', $paramClass, $slugAttributeName, $slugInput));
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'deskpro_slug';
    }

    protected function getOptions(ParamConverter $configuration)
    {
        return array_replace(
            [
                'slug_col'         => 'slug',
                'slug_route_param' => 'slug',
            ], $configuration->getOptions()
        );
    }

    private function isContentClass($param_class)
    {
        switch ($param_class) {
            case Article::class:
            case Feedback::class:
            case News::class:
            case Download::class:
            case Topic::class:
            case Guide::class:
                return true;
            default:
                return false;
        }
    }
}
