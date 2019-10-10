<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Entity\ContentTemplate;
use DeskPRO\Bundle\AppBundle\Entity\ContentTemplateAttachment;
use DeskPRO\Bundle\AppBundle\Form\Type\Content\ContentTemplateType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContentTemplatesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/content_templates")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\ContentTemplate")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Content\ContentTemplateType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\ContentTemplate"
 *      }
 *     }
 * )
 * @RequireAgentPermissions()
 */
class ContentTemplatesController extends CrudController
{
    public static $entity       = ContentTemplate::class;
    public static $type         = ContentTemplateType::class;
    public static $listSort     = 'id';
    public static $listOrder    = 'asc';
    public static $listPaginate = false;

    /**
     * @ApiDoc(
     *      description="Create a new resource",
     *      tags={"CRUD"="#ffa500"},
     *      statusCodes={
     *          201="Returned in case of successful resource creation",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $this->checkExposed(__METHOD__);
        $this->denyAccessUnlessGranted(PermissionGroupVoter::CREATE, $this->getPermissionGroupContext($request));

        $em = $this->getManager();
        $model = $this->instantiateEntity($request);

        foreach ($request->get('attach') as $blob_id) {
            $blob = $em->getRepository('DeskPRO:Blob')->find($blob_id);
            if ($blob) {
                $attach = new ContentTemplateAttachment();
                $attach
                    ->setPerson($this->getUser())
                    ->setBlob($blob); // ->setIsTemp(false)
                $em->persist($attach);
                $em->persist($blob);
                $model->addAttachment($attach);
            }
        }
        return $this->handleForm($model, $request);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'person'             => $this->getUser(),
            'allow_extra_fields' => true,
        ]);

        return parent::handleForm($model, $request, $options);
    }
}
