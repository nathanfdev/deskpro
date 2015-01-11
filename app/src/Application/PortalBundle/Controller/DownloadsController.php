<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Controller;


use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DownloadsController extends AbstractController
{
    /**
     * @Route("/downloads", name="portal_downloads")
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function indexAction(Request $request)
    {
        return $this->renderThemeView('Theme:Downloads:index.html.twig');
    }

    /**
     * @Route("/downloads/{slug}", name="portal_downloads_browse")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function browseAction(Request $request, DownloadCategory $category)
    {
        return $this->renderThemeView(
            'Theme:Downloads:browse.html.twig',
            array(
                'category' => $category,
                'page' => $request->query->get('page', 1),
                'count' => 2,
                'show_pagination' => true
            )
        );
    }


    /**
     * @Route("/downloads/files/{slug}", name="portal_downloads_view")
     * @ParamConverter(name="file", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function viewAction(Request $request, Download $file)
    {
        $related = $this->getDownloadsDataService()->getRelatedFiles($file);
        $rating = $this->getRatingsHelper()->getPersonRating($file, $this->getUser());

        return $this->renderThemeView(
            'Theme:Downloads:view.html.twig',
            array(
                'download' => $file,
                'related_files' => $related,
                'rating' => $rating
            )
        );
    }


    /**
     * @Route("/downloads/files/{slug}/download", name="portal_downloads_download")
     * @ParamConverter(name="download", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function downloadAction(Request $request, Download $file)
    {
        return new Response('downlading file...');
    }

    /**
     * @Route("/downloads/files/{slug}/vote-up", name="portal_downloads_vote_up", defaults={"up_or_down":"up"})
     * @Route("/downloads/files/{slug}/vote-down", name="portal_downloads_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="file", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS') and is_granted('RATE_DOWNLOADS')")
     */
    public function downloadRateAction(Download $file, $visitor_id, $up_or_down)
    {
        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        if ('down' === $up_or_down) {
            $this->getRatingsHelper()->rateContentDown($file, $visitor_id, $person);
        } else {
            $this->getRatingsHelper()->rateContentUp($file, $visitor_id, $person);
        }

        return $this->redirectToRoute('portal_downloads_view', array('slug' => $file->getSlug()));
    }

    /**
     * @return \Application\AppBundle\DataService\DownloadsDataService
     */
    public function getDownloadsDataService()
    {
        return $this->get('data.downloads');
    }
}
