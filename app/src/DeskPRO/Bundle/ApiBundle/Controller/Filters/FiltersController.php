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
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Filters;


use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Entity\Filter;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @RouteResource("filters")
 */
class FiltersController extends BaseController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *      description="create a sandbox widget",
     *      input={"class"="filter","name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\Filter"
     * )
     */
    public function postAction(Request $request)
    {
        $filter = new Filter();

        return $this->handleFormSubmission($request, $filter);
    }

    /**
     * we will be making this more abstract for general use by other controllers
     */
    protected function handleFormSubmission(Request $request, Filter $filter)
    {
        $status = $filter->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        $form = $this->get('form.factory')->createNamedBuilder(null, 'filter', $filter)->getForm();

        $submitted = $request->request->all();

        if (!count($submitted)) {
            throw new BadRequestHttpException();
        }

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {

            $this->getDoctrine()->getManager()->persist($filter);
            $this->getDoctrine()->getManager()->flush($filter);

//            return View::create(
//                $this->createRepresentation($filter),
//                $status,
//                array(
//                    'Location' => $this->generateUrl('get_sandbox_widgets', array('id' => $filter->getId()))
//                )
//            );
        }

        throw new InvalidFormException($form); // let our listeners generate the form error response
    }
}
