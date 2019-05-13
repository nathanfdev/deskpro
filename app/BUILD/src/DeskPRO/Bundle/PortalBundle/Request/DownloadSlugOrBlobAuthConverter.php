<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Request;

use Application\DeskPRO\Entity\Download;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Used to find Download by slug or by blob authcode.
 */
class DownloadSlugOrBlobAuthConverter extends DeskproSlugConverter
{
    public function apply(Request $request, ParamConverter $configuration)
    {
        $paramName         = $configuration->getName();
        $paramOptions      = $this->getOptions($configuration);
        $paramClass        = $configuration->getClass();
        $slugAttributeName = $paramOptions['slug_route_param'];
        $slugOrBlobAuth    = $request->attributes->get($slugAttributeName);

        if ($paramClass !== Download::class) {
            return parent::apply($request, $configuration);
        }

        $blob = $this->em->getRepository('DeskPRO:Blob')->getByAuthCode($slugOrBlobAuth);
        if (!$blob) {
            return parent::apply($request, $configuration);
        }

        $downloads = $this->em->getRepository('DeskPRO:Download')->findByBlob($blob);
        if (!$downloads) {
            return parent::apply($request, $configuration);
        }

        // is it possible that several downloads have same blob?
        if (count($downloads) > 1) {
            throw new NotFoundHttpException(sprintf('Multiple downloads have same blob with authcode "%s"', $slugOrBlobAuth));
        }

        $download = array_pop($downloads);

        $request->attributes->set($paramName, $download);
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'deskpro_download_slug_or_blob_auth';
    }
}
