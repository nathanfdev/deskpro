<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Approvals;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Form\Type\Approval\ApprovalTemplateType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Form\FormInterface;

/**
 * Class ApprovalTemplatesController.
 *
 * @ApiModes("all")
 * @ApiUserContext("admin", agent={"list", "get", "count"})
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
}
