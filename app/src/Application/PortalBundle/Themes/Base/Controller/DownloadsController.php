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

namespace Application\PortalBundle\Themes\Base\Controller;


use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Symfony\Component\HttpFoundation\Request;
use Application\PortalBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class DownloadsController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('Theme:Downloads:index.html.twig');
    }

    /**
     * @ParamConverter(name="category", converter="deskpro_slug")
     */
    public function browseAction(DownloadCategory $category)
    {
        return $this->render('Theme:Downloads:browse.html.twig', array(
                'cat' => $category
            )
        );
    }


    /**
     * @ParamConverter(name="download", converter="deskpro_slug")
     */
    public function viewAction(Download $download)
    {
        return $this->render('Theme:Downloads:view.html.twig', array(
                'download' => $download
            )
        );
    }


    /**
     * @ParamConverter(name="download", converter="deskpro_slug")
     */
    public function downloadAction(Download $download)
    {
        return new Response('downlading file...');
    }


    public function listAction(Request $request)
    {
        $options_resolver = new OptionsResolver();
        $options_resolver
            ->setDefaults(
                array(
                    'count' => 10,
                    'style' => 'small'
                )
            )
            ->setAllowedValues(
                array(
                    'style' => array('small')
                )
            );
        $options = $options_resolver->resolve($request->query->get('tag_options'));

        $downloads  = $this->getDownloadsRepo()->getNewest($options['count']);
        $total = $this->getDownloadsRepo()->countPublished();

        return $this->render(
            sprintf('Theme:Downloads:list_%s.html.twig', $options['style']),
            array(
                'count_downloads' => $total,
                'downloads'    => $downloads
            )
        );
    }


    public function catsAction(Request $request)
    {
        $options_resolver = new OptionsResolver();
        $options_resolver
            ->setDefaults(
                array(
                    'parent' => null,
                    'style'  => 'small'
                )
            )
            ->setAllowedValues(
                array(
                    'style' => array('small')
                )
            );
        $options = $options_resolver->resolve($request->query->get('tag_options'));

        if ($category = $options['parent']) {
            if (!$category instanceof DownloadCategory) {
                $category = $this->getDownloadCategoriesRepo()->find($category);
            }
            $categories = $category->children;
        } else {
            $categories = $this->getDownloadCategoriesRepo()->findBy(array('parent' => $category));
        }

        return $this->render(
            sprintf('Theme:Downloads:cats_%s.html.twig', $options['style']),
            array(
                'cat'        => $category,
                'child_cats' => $categories
            )
        );
    }


    /**
     * @return \Application\DeskPRO\EntityRepository\Download
     */
    protected function getDownloadsRepo()
    {
        return $this->getDoctrine()->getRepository('DeskPRO:Download');
    }


    /**
     * @return \Application\DeskPRO\EntityRepository\DownloadCategory
     */
    protected function getDownloadCategoriesRepo()
    {
        return $this->getDoctrine()->getRepository('DeskPRO:DownloadCategory');
    }
}
