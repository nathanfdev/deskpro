<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Reports;

use Application\DeskPRO\Entity\ReportWidget;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Reports\ReportWidgetType;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ReportWidgetsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/report_widgets")
 * @ApiDoc(target="all", section="Reports", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Reports\ReportWidget")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Reports\ReportWidgetType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\ReportWidget"
 *      }
 *     }
 * )
 * @Feature("new_reports")
 */
class ReportWidgetsController extends CrudController
{
    public static $entity       = ReportWidget::class;
    public static $type         = ReportWidgetType::class;
    public static $listOrder    = 'asc';
    public static $listSort     = 'display_order';
    public static $listPaginate = false;

    /**
     * todo temporary copied from the legacy api
     * todo refactor to RecordStore on the client side (agents, agent teams, departments) and remove the action.
     *
     * @Rest\Get("/group-params")
     *
     * @return View
     */
    public function getGroupParamsAction()
    {
        return new View($this->container->get('reports.widget.service')->getGroupParams());
    }

    /**
     * @Rest\Post("/test/{reportWidget}")
     *
     * @param ReportWidget $reportWidget
     * @param Request      $request
     *
     * @return View
     */
    public function testAction(ReportWidget $reportWidget, Request $request)
    {
        $form = $this->createForm(static::$type, $reportWidget, ['display_only' => true]);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        return new View($this->wrap($reportWidget));
    }

    /**
     * @Rest\Post("/parse")
     *
     * @param Request $request
     *
     * @return View
     */
    public function parseAction(Request $request)
    {
        $parts       = $request->request->get('parts');
        $query       = $request->request->get('query');
        $currentType = $request->request->get('current_type');
        $newType     = $request->request->get('new_type');

        if ($currentType === 'builder' && $newType === 'query') {
            $results = ['query' => SelectPart::getQueryStringFromParts($parts)];
        } elseif ($currentType === 'query' && $newType === 'builder') {
            if (!$query) {
                $statement = $this->container->get('dpql.statement_factory')->createSelectPart([], '');
            } else {
                $statement = $this->container->get('dpql.compiler')->compile($query);
            }
            try {
                $results = ['parts' => $statement->getDpqlPartsForInput()];
            } catch (DpqlException $e) {
                $results = ['error' => $e->getMessage()];
            }
        } else {
            $results = [
                'error' => 'Unknown conversion action.',
            ];
        }

        return new View($results);
    }

    /**
     * {@inheritdoc}
     *
     * @param ReportWidget $entity
     */
    protected function deleteEntity($entity)
    {
        if (!$entity->isCustom()) {
            throw $this->createBadRequestException('You can delete only custom reports.');
        }

        parent::deleteEntity($entity);
    }
}
