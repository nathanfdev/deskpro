<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Reports;

use Application\DeskPRO\Entity\ReportDashboardShareableLink;
use Application\DeskPRO\Entity\ReportDashboardShareableShortUrl;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Type\Reports\ReportDashboardShareableLinkType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class DashboardShareableLinksController.
 *
 * @ApiModes("all")
 * @Rest\Route("/dashboard_shareable_links")
 * @ApiDoc(target="all", section="Reports", output="Application\DeskPRO\Entity\ReportDashboardShareableLink")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Reports\ReportDashboardShareableLinkType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\ReportDashboardShareableLink"
 *      }
 *     }
 * )
 *
 * @Feature("new_reports")
 */
class DashboardShareableLinksController extends CrudController
{
    public static $entity = ReportDashboardShareableLink::class;
    public static $type   = ReportDashboardShareableLinkType::class;

    /**
     * @param HttpKernelInterface $kernel
     * @param Request             $masterRequest
     * @param array               $params
     *
     * @throws \Exception
     *
     * @return Response
     */
    public static function subRequestSearch(HttpKernelInterface $kernel, Request $masterRequest, array $params)
    {
        $request = $masterRequest->duplicate(
            array_merge($params, $masterRequest->query->all()),
            null,
            ['_controller' => 'ApiBundle:Reports\DashboardShareableLinks:list']
        );
        $request->query->add($params);

        return $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @Rest\Post("/{id}/create_short_url", requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function createShortUrlAction(Request $request, $id)
    {
        $this->checkExposed(__METHOD__);
        $this->denyAccessUnlessGranted(PermissionGroupVoter::MODIFY, $this->getPermissionGroupEntityContext($id, $request));

        $shareableLink = $this->findEntity($id, $request);

        $shortUrl = new ReportDashboardShareableShortUrl();
        $shortUrl->setShareableLink($shareableLink);

        $this->getManager()->persist($shortUrl);
        $this->getManager()->flush();

        return new View($this->wrap($shortUrl));
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->join("$alias.dashboard", 'd');

        if ($request->get('dashboard')) {
            $qb->andWhere("$alias.dashboard = :dashboard");
            $qb->setParameter('dashboard', $request->get('dashboard'));
        }
    }
}
