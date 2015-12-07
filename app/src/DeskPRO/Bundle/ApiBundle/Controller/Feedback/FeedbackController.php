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

namespace DeskPRO\Bundle\ApiBundle\Controller\Feedback;

use Application\DeskPRO\Entity\CustomDataFeedback;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackCountCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackSelectCriteria;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * API access to feedback.
 */
class FeedbackController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get a filtered list of feedback",
     *      parameters={
     *          {
     *              "name"="status",
     *              "requirement"="\w+",
     *              "description"="filter by status",
     *              "dataType"="string",
     *              "required"=false
     *          },
     *          {
     *              "name"="status_category",
     *              "requirement"="\w+",
     *              "description"="filter by status category",
     *              "dataType"="string",
     *              "required"=false
     *          },
     *          {
     *              "name"="page",
     *              "requirement"="\d+",
     *              "description"="the page you are requesting",
     *              "dataType"="integer",
     *              "required"=false
     *          },
     *          {
     *              "name"="count",
     *              "requirement"="\d+",
     *              "description"="results per page",
     *              "dataType"="integer",
     *              "required"=false
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/feedback/", name="api_feedback")
     *
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $dataService = $this->get('data.feedback');
        $params = $request->query->all();
        try {
            $criteria = FeedbackSelectCriteria::fromParameters($params, new OptionsResolver());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 5);

        $feedback = $dataService->selectFeedback($criteria, $page, $count);

        return View::create(
            $this->dataSerialize($feedback),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Get feedback counts",
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Get("/feedback/counts", name="api_feedback_count")
     *
     * @param Request $request
     *
     * @throws \LogicException
     * @throws AccessException
     * @throws UndefinedOptionsException
     * @throws BadRequestHttpException
     *
     * @return View
     */
    public function getCountsAction(Request $request)
    {
        $dataService = $this->get('data.feedback');
        $params = $request->query->all();
        try {
            $criteria = FeedbackCountCriteria::fromParameters($params, new OptionsResolver());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        $count = $dataService->countFeedback($criteria);

        return View::create(
            $this->createRepresentation($count),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Mass action on set of feedback",
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Put("/feedback/mass_action", name="api_feedback_mass_action")
     *
     * @param Request $request
     *
     * @throws \LogicException
     * @throws AccessException
     * @throws UndefinedOptionsException
     * @throws BadRequestHttpException
     *
     * @return View
     */
    public function massAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ids = $request->query->get('id');
        if (count($ids) > 0) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select('f')
                ->from('DeskPRO:Feedback', 'f')
                ->andWhere('f.id IN (:ids)')
                ->setParameter('ids', $ids);

            $feedback = $qb->getQuery()->getResult();

            $params = $request->request->all();
            foreach ($params as $param => $value) {
                if ($param === 'category') {
                    $category = $em->getRepository('DeskPRO:FeedbackCategory')->find($value);
                    if (null !== $category) {
                        foreach ($feedback as $item) {
                            $item->setCategory($category);
                        }
                    }
                } elseif ($param === 'status') {
                    foreach ($feedback as $item) {
                        $item->setStatus($value);
                    }
                } elseif ($param === 'status_category') {
                    $statusCategory = $em->getRepository('DeskPRO:FeedbackStatusCategory')
                        ->findOneBy(['title' => $value]);
                    if (null !== $statusCategory) {
                        foreach ($feedback as $item) {
                            $item->setStatusCategory($statusCategory);
                            $item->setStatus($statusCategory->getStatusType());
                        }
                    }
                } elseif ($param === 'hidden_status') {
                    foreach ($feedback as $item) {
                        $item->setStatus(Feedback::STATUS_HIDDEN);
                        $item->setHiddenStatus($value);
                        $em->persist($item);
                        $em->flush();
                    }
                } elseif ($param === 'custom_category') {
                    $customDef = $em->getRepository('DeskPRO:CustomDefFeedback')->findOneBy(['title' => 'category']);
                    foreach ($feedback as $item) {
                        $customCategory = $em->getRepository('DeskPRO:CustomDataFeedback')
                            ->findOneBy(['feedback' => $item, 'field' => $customDef]);
                        if (null === $customCategory) {
                            $customCategory = new CustomDataFeedback();
                            $customCategory->setFeedback($item);
                            $customCategory->setField($customDef);
                        }
                        $customCategory->setInput($value);
                        $em->persist($customCategory);
                    }
                }
            }
            $em->flush();
        }
    }

    /**
     * @APIDoc(
     *      description="delete feedback",
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      }
     * )
     * @Delete("/feedback", name="api_feedback_delete")
     *
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return View
     */
    public function deleteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ids = $request->get('id');
        $qb = $em->createQueryBuilder();
        $qb
            ->select('f')
            ->from('DeskPRO:Feedback', 'f')
            ->andWhere('f.id IN (:ids)')
            ->setParameter('ids', $ids);

        $feedbackCollection = $qb->getQuery()->getResult();
        foreach ($feedbackCollection as $feedback) {
            $em->remove($feedback);
        }

        $em->flush();

        return View::create(
            array(),
            Response::HTTP_OK
        );
    }

}
