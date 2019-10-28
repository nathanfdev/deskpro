<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Approvals;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalTemplateType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApprovalTemplatesController.
 *
 * @ApiModes("all")
 * @ApiUserContext("admin", agent={"list", "get", "count", "getTicketApprovers"})
 * @Rest\Route("/approval_templates")
 * @ApiDoc(
 *     target="all",
 *     section="Approvals",
 *     output="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalTemplateType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate",
 *      }
 *     }
 * )
 * @RequireAgentPermissions()
 */
class ApprovalTemplatesController extends CrudController
{
    public static $entity       = ApprovalTemplate::class;
    public static $type         = ApprovalTemplateType::class;
    public static $listPaginate = true;
    public static $listOrder    = 'ASC';
    public static $listSort     = 'id';

    /**
     * {@inheritdoc}
     */
    protected function persistModel($model, FormInterface $form = null)
    {
        $em = $this->getManager();

        if (!$em->contains($model) && $model instanceof ApprovalTemplate) {
            $model->addDefaultSendTicketApprovalEmailActions();
        }

        $em->persist($model);
        $em->flush();

        return $model;
    }

    /**
     * @ApiDoc(
     *      description="Get a list of approvers",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="We will return such status in case we found your entity",
     *          404="Not Found error will returned in case we can't find entity with specified ID"
     *      }
     * )
     * @Rest\Get("/{id}/ticket/{ticket}/approvers", requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param Ticket  $ticket
     * @param int     $id
     *
     * @return View
     */
    public function getTicketApproversAction($id, Ticket $ticket, Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW, $this->getPermissionGroupEntityContext($id, $request));

        /** @var ApprovalTemplate $template */
        $template = $this->findEntity($id, $request);

        if ($template->canChooseApprovers()) {
            $criteria = $template->getApproverSelectionCriteria();

            $hasTicketUser    = $criteria->canSelectTicketUser();
            $hasOrgManagers   = $criteria->canSelectOrganizationManagers();
            $hasAllAgents     = $criteria->canSelectFromAllAgents();
            $predefinedPeople = $criteria->getSelectFromPeople();
        } else {
            $criteria = $template->getSelectedApprovers();

            $hasTicketUser    = $criteria->hasTicketUser();
            $hasOrgManagers   = $criteria->hasOrganizationManagers();
            $hasAllAgents     = $criteria->hasAllAgents();
            $predefinedPeople = $criteria->hasOrganizationManagers();
        }

        $em = $this->getManager();
        $qb = $em->createQueryBuilder();

        $subWhere = $em->getExpressionBuilder()->orX();
        $subWhere->add('1 = 0');

        if ($hasTicketUser) {
            $subWhere->add('p.id = :ticket_user');
            $qb->setParameter('ticket_user', $ticket->getPerson()->getId());
        }
        if ($hasOrgManagers && $ticket->getPerson()->getOrganizationId()) {
            $subWhere->add('p.organization_manager = 1 AND p.organization = :org_id');
            $qb->setParameter('org_id', $ticket->getPerson()->getOrganizationId());
        }
        if ($hasAllAgents) {
            $subWhere->add('p.is_agent = 1');
        }
        if ($predefinedPeople) {
            $subWhere->add('p.id IN (:predefined_people)');
            $qb->setParameter('predefined_people', $predefinedPeople);
        }

        $qb
            ->select('p')
            ->from(Person::class, 'p')
            ->where('p.is_deleted = 0 AND p.is_disabled = 0')
            ->andWhere($subWhere)
        ;

        return new View($this->wrap($qb->getQuery()->getResult()));
    }
}
