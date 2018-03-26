<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\TicketEscalation;
use Application\DeskPRO\Tickets\Filters\FilterTerms;
use Application\DeskPRO\Tickets\Filters\LegacyTermsTransformer;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Operations about Ticket escalations.
 *
 * SWG\Resource(
 * 	resourcePath="/ticket_escalations",
 * 	description="Operations about Ticket escalations",
 * 	basePath="/api"
 * )
 *
 * @ApiModes("all")
 */
class TicketEscalationsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listAction');

        return $multi;
    }

    /**
     * @return JsonResponse
     *
     * SWG\Api(
     * 	path="/ticket_escalations",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Get list of ticket escalations",
     * 		notes="",
     *		type="array",
     *  )
     * )
     */
    public function listAction()
    {
        $escalations = $this->em->getRepository('DeskPRO:TicketEscalation')->getEscalations();
        $data        = $this->getApiData($escalations, false);

        return $this->createApiResponse([
            'escalations' => $data,
        ]);
    }

    //###################################################################################################################
    // get
    //###################################################################################################################

    /**
     * @param $id
     *
     * @return JsonResponse
     *
     * SWG\Api(
     * 	path="/ticket_escalations/{id}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Get escalation by ID",
     * 		notes="",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
     *				name="id",
     *				description="Escalation ID",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *      )
     *  )
     * )
     */
    public function getAction($id, $special_type = null)
    {
        /** @var \Application\DeskPRO\EntityRepository\TicketEscalation $rep */
        $rep = $this->em->getRepository('DeskPRO:TicketEscalation');
        $esc = $special_type ? $rep->getSpecialEscalation($special_type, $id) : $rep->find($id);

        if (!$esc) {
            throw new NotFoundHttpException();
        }

        $trans = new LegacyTermsTransformer();
        $crit  = $trans->toFilterTerms($esc->terms);
        $crit2 = $trans->toFilterTerms($esc->terms_any);

        $esc              = $this->getApiData($esc);
        $esc['terms']     = $crit->exportToArray();
        $esc['terms_any'] = $crit2->exportToArray();

        return $this->createApiResponse([
            'escalation' => $esc,
        ]);
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     *
     * SWG\Api(
     * 	path="/ticket_escalations/{id}",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Update existing escalation by ID",
     * 		notes="",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
     *				name="id",
     *				description="Escalation ID",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *          SWG\Parameter(
     *				name="title",
     *				description="Escalation name",
     *				paramType="query",
     *				required=false,
     *				type="string",
     *			),
     *          SWG\Parameter(
     *				name="event_trigger",
     *				description="Event trigger name",
     *				paramType="query",
     *				required=false,
     *				type="string",
     *			),
     *          SWG\Parameter(
     *				name="event_trigger_time",
     *				description="When to run event_trigger",
     *				paramType="query",
     *				required=false,
     *				type="integer",
     *			),
     *          SWG\Parameter(
     *				name="terms",
     *				description="Criteria for trigger run",
     *				paramType="query",
     *				required=false,
     *				type="string",
     *			),
     *          SWG\Parameter(
     *				name="terms_any",
     *				description="Filter",
     *				paramType="query",
     *				required=false,
     *				type="string",
     *			),
     *          SWG\Parameter(
     *				name="actions",
     *				description="Array of actions to perform",
     *				paramType="query",
     *				required=false,
     *				type="string",
     *			),
     *      )
     *  )
     * )
     *
     * SWG\Api(
     * 	path="/ticket_escalations",
     * 	SWG\Operation(
     * 		method="PUT",
     * 		summary="Create new escalation",
     * 		notes="",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
     *				name="title",
     *				description="Escalation name",
     *				paramType="query",
     *				required=false,
     *				type="string",
     *			),
     *          SWG\Parameter(
     *				name="event_trigger",
     *				description="Event trigger name",
     *				paramType="query",
     *				required=false,
     *				type="string",
     *			),
     *          SWG\Parameter(
     *				name="event_trigger_time",
     *				description="When to run event_trigger",
     *				paramType="query",
     *				required=false,
     *				type="integer",
     *			),
     *          SWG\Parameter(
     *				name="terms",
     *				description="Criteria for trigger run",
     *				paramType="query",
     *				required=false,
     *				type="string",
     *			),
     *          SWG\Parameter(
     *				name="terms_any",
     *				description="Filter",
     *				paramType="query",
     *				required=false,
     *				type="string",
     *			),
     *          SWG\Parameter(
     *				name="actions",
     *				description="Array of actions to perform",
     *				paramType="query",
     *				required=false,
     *				type="string",
     *			),
     *      )
     *  )
     * )
     */
    public function saveAction($id, $special_type = null)
    {
        /** @var \Application\DeskPRO\EntityRepository\TicketEscalation $rep */
        $rep = $this->em->getRepository(TicketEscalation::class);

        if ($special_type) {
            $esc = $rep->getSpecialEscalation($special_type, $id);
        } elseif ($id) {
            $esc = $rep->find($id);
        } else {
            $esc = new TicketEscalation();
        }

        $esc->title              = $this->in->getString('title');
        $esc->event_trigger      = $this->in->getString('event_trigger');
        $esc->event_trigger_time = $this->in->getUint('event_trigger_time') ?: 1;

        $crit = new FilterTerms();
        foreach ($this->in->getArrayValue('terms') as $term_info) {
            $crit->addTermFromArray($term_info);
        }

        $trans      = new LegacyTermsTransformer();
        $esc->terms = $trans->toLegacyTerms($crit);

        $crit = new FilterTerms();
        foreach ($this->in->getArrayValue('terms_any') as $term_info) {
            $crit->addTermFromArray($term_info);
        }

        $trans          = new LegacyTermsTransformer();
        $esc->terms_any = $trans->toLegacyTerms($crit);

        $action_defs = $this->container->getTicketActionDefManager();
        $actions     = new TriggerActions();
        foreach ($this->in->getArrayValue('actions') as $act) {
            if ($act) {
                $type = $act['type'];

                if ($action_defs->hasNamedDef($type)) {
                    $act['type_class'] = $action_defs->getNamedDef($type)->getDef()->getTriggerActionClass();
                    if (!$act['type_class']) {
                        continue;
                    }
                }
                $actions->addActionFromArray($act);
            }
        }
        $esc->actions = $actions;

        $this->em->persist($esc);
        $this->em->flush();

        return $this->createSuccessResponse([
            'escalation_id' => $esc->id,
        ]);
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     *
     * SWG\Api(
     * 	path="/ticket_escalations/{id}",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="Delete escalation by ID",
     * 		notes="",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
     *				name="id",
     *				description="Escalation ID",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *      )
     *  )
     * )
     */
    public function deleteAction($id)
    {
        $esc = $this->em->getRepository('DeskPRO:TicketEscalation')->find($id);

        if (!$esc) {
            throw new NotFoundHttpException();
        }

        $this->em->remove($esc);
        $this->em->flush();

        return $this->createSuccessResponse([
            'old_id' => $id,
        ]);
    }

    /**
     * Enable/disable escalation.
     *
     * @param $id
     * @param $is_enabled - controlled by router
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return JsonResponse
     * @return JsonResponse
     */
    public function toggleEscalationAction($id, $is_enabled)
    {
        $trigger = $this->em->find('DeskPRO:TicketEscalation', $id);
        if (!$trigger) {
            throw new NotFoundHttpException();
        }

        if ($trigger->is_enabled = $is_enabled) {
            $trigger->date_created = new \DateTime();
        }
        $this->em->persist($trigger);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @return JsonResponse
     *
     * SWG\Api(
     * 	path="/ticket_escalations/run_order",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Update escalation run order",
     * 		notes="",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
     *				name="run_order",
     *				description="Escalation ID",
     *				paramType="query",
     *				required=true,
     *				type="integer[]",
     *			),
     *      )
     *  )
     * )
     */
    public function saveRunOrderAction()
    {
        $run_order = $this->in->getCleanValueArray('run_order', 'uint', 'discard');
        $this->em->getRepository('DeskPRO:TicketEscalation')->updateRunOrder($run_order);

        return $this->createSuccessResponse();
    }
}
