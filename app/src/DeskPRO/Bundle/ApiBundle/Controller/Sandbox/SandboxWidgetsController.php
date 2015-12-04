<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\ApiBundle\Controller\Sandbox;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Model\PrimitiveArray;
use DeskPRO\Bundle\AppBundle\Entity\SandboxWidget;
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * THIS IS A TEST CONTROLLER AND IS NOT PRODUCTION CODE. It is used for testing the api, and that is it.
 */
class SandboxWidgetsController extends BaseController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *      description="get a collection of sandbox widgets",
     *      filters={
     *          {
     *              "name"="count",
     *              "dataType"="integer",
     *              "requirement"="\d+",
     *              "description"="how many objects to return"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          400="Invalid request"
     *      }
     * )
     *
     * @Get("/sandbox_widgets", name="api_sandbox_widgets")
     */
    public function cgetAction(Request $request)
    {
        $type = $request->get('type');
        if ($type) {
            $widgets = $this->getDoctrine()->getManager()->getRepository('App:SandboxWidget')->findBy(array('type' => $type));
        } else {
            $widgets = $this->getDoctrine()->getManager()->getRepository('App:SandboxWidget')->findAll();
        }

        $pager = new Pagerfanta(new ArrayAdapter($widgets));
        $pager->setMaxPerPage(2);
        $pager->setCurrentPage($request->query->get('page', 1));

        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a sandbox widget",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the sandbox widget",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\SandboxWidget"
     * )
     *
     * @Get("/sandbox_widgets/{id}", name="api_sandbox_widgets_get")
     */
    public function getAction(Request $request, $id)
    {
        $widget = $this->getWidget($id);

        return View::create(
            $this->dataSerialize($widget),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="create a sandbox widget",
     *      input={"class"="sandbox_widget","name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\SandboxWidget"
     * )
     *
     * @Post("/sandbox_widgets", name="api_sandbox_widgets_post")
     */
    public function postAction(Request $request)
    {
        $widget = new SandboxWidget();

        return $this->handleFormSubmission($request, $widget);
    }

    /**
     * @ApiDoc(
     *      description="modify a sandbox widget",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the sandbox widget",
     *              "dataType"="integer"
     *          }
     *      },
     *      input={"class"="sandbox_widget","name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\SandboxWidget"
     * )
     *
     * @Put("/sandbox_widgets/{id}", name="api_sandbox_widgets_put")
     */
    public function putAction(Request $request, $id)
    {
        $widget = $this->getWidget($id);

        return $this->handleFormSubmission($request, $widget);
    }

    /**
     * @ApiDoc(
     *      description="delete a sandbox widget",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the sandbox widget",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Deleted",
     *          404="Not Found"
     *      }
     * )
     *
     * @Delete("/sandbox_widgets/{id}", name="api_sandbox_widgets_delete")
     */
    public function deleteAction($id)
    {
        $widget = $this->getWidget($id);

        $this->getDoctrine()->getManager()->remove($widget);
        $this->getDoctrine()->getManager()->flush();

        return View::create(
            [],
            Response::HTTP_OK
        );
    }

    /**
     * we will be making this more abstract for general use by other controllers.
     */
    protected function handleFormSubmission(Request $request, SandboxWidget $widget)
    {
        $status = $widget->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        $form = $this->get('form.factory')->createNamedBuilder(null, 'sandbox_widget', $widget)->getForm();

        $submitted = $request->request->all();

        if (!count($submitted)) {
            throw new BadRequestHttpException();
        }

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($widget);
            $this->getDoctrine()->getManager()->flush($widget);

            return View::create(
                $this->dataSerialize($widget),
                $status,
                array(
                    'Location' => $this->generateUrl('api_sandbox_widgets_get', array('id' => $widget->getId())),
                )
            );
        }

        throw new InvalidFormException($form); // let our listeners generate the form error response
    }

    /**
     * @param $id
     *
     * @return SandboxWidget
     */
    private function getWidget($id)
    {
        $widget = $this->getDoctrine()->getManager()->getRepository('App:SandboxWidget')->find($id);

        if (!$widget) {
            throw new NotFoundHttpException();
        }

        return $widget;
    }

    /**
     * @Get("/sandbox_widget_types", name="api_sandbox_widget_types")
     */
    public function getTypesAction()
    {
        $types = $this->getDoctrine()->getRepository('App:SandboxWidget')->getWidgetTypes();

        return View::create(
            $this->dataSerialize(new PrimitiveArray($types)),
            Response::HTTP_OK
        );
    }
}
