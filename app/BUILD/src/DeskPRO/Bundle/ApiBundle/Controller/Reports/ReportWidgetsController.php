<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Reports;

use Application\DeskPRO\Entity\ReportWidget;
use Application\DeskPRO\Entity\TmpData;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Reports\ReportWidgetType;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContextStorage;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\ReportsRendererInterface;
use DeskPRO\Bundle\ReportBundle\Reports\SplitResult;
use DeskPRO\Bundle\ReportBundle\Reports\SplitResults;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $this->getContainer()->get('dpql.context_storage')->setMode(DpqlContextStorage::MODE_EDIT);

        return parent::postAction($request);
    }

    /**
     * @Rest\Get("/{id}", requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getAction(Request $request, $id)
    {
        $this->getContainer()->get('dpql.context_storage')->setMode(DpqlContextStorage::MODE_EDIT);

        return parent::getAction($request, $id);
    }

    /**
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        $this->getContainer()->get('dpql.context_storage')->setMode(DpqlContextStorage::MODE_EDIT);

        return parent::listAction($request);
    }

    /**
     * @Rest\Put("/{id}", requirements={"id"="\d+"})
     *
     * @param int     $id
     * @param Request $request
     *
     * @SerializerView(serializeNull=true)
     *
     * @return View
     */
    public function putAction($id, Request $request)
    {
        $this->getContainer()->get('dpql.context_storage')->setMode(DpqlContextStorage::MODE_EDIT);

        return parent::putAction($id, $request);
    }

    /**
     * @todo temporary copied from the legacy api
     * @todo refactor to RecordStore on the client side (agents, agent teams, departments) and remove the action
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
     * @Rest\Post("/download/{reportWidget}/{type}")
     *
     * @param ReportWidget $reportWidget
     * @param string       $type
     * @param Request      $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function downloadAction(ReportWidget $reportWidget, $type, Request $request)
    {
        $form = $this->createForm(static::$type, $reportWidget, ['display_only' => true]);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $query     = $reportWidget->getQuery();
        $variables = $reportWidget->getVariables();

        $results = $this->getContainer()->get('reports.dashboard_widget.service')->doRender(
            $query,
            ['variables' => $variables],
            ReportsRendererInterface::TYPE_TABLE,
            $type,
            $this->getUser()
        );
        $renderer = $this->get('reports.renderer_registry')->getRenderer(ReportsRendererInterface::TYPE_TABLE, $type);

        if ($results instanceof SplitResults) {
            $actualResults = '';
            foreach ($results->getResults() as $result) {
                /* @var SplitResult $result */
                $actualResults .= $result->getTitle().";\r\n".$result->getResults()."\r\n";
            }
        } elseif ($results instanceof SplitResult) {
            $actualResults = $results->getTitle().";\r\n".$results->getResults()."\r\n";
        } else {
            $actualResults = $results;
        }

        $tmpData = new TmpData();
        $tmpData
            ->setDateExpire(new \DateTime('+15 minutes'))
            ->setData('content', $actualResults)
            ->setData('content_type', $renderer->getContentType())
            ->setData('content_disposition', 'attachment; filename='.$reportWidget->getTitle().'.'.$renderer->getExtension())
            ->setData('content_length', strlen($actualResults));

        $this->getContainer()->getEm()->persist($tmpData);
        $this->getContainer()->getEm()->flush($tmpData);

        return new View($this->wrap(['auth' => $tmpData->getAuth()]));
    }

    /**
     * @Rest\Get("/download/generated/{auth}")
     *
     * @param string $auth
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function downloadGeneratedFileAction($auth)
    {
        $tmpData = $this->getContainer()->getEm()->getRepository(TmpData::class)->findOneBy(['auth' => $auth]);

        if (!$tmpData) {
            throw new NotFoundHttpException();
        }

        $response = new Response();
        $response->headers->set('Content-Type', $tmpData->getData('content_type'));
        $response->headers->set('Content-Disposition', $tmpData->getData('content_disposition'));
        $response->headers->set('Content-Length', $tmpData->getData('content_length'));
        $response->setContent($tmpData->getData('content'));

        return $response;
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
