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
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
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
     * @throws \Exception
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $this->checkExposed(__METHOD__);
        $this->denyAccessUnlessGranted(PermissionGroupVoter::CREATE, $this->getPermissionGroupContext($request));

        /** @var ContentTemplate $model */
        $model = $this->instantiateEntity($request);
        $this->handleAttachments($model, $request);

        return $this->handleForm($model, $request);
    }

    /**
     * @ApiDoc(
     *      description="Update an existing resource",
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
     *          204="Returned in case of successful resource modify",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Put("/{id}", requirements={"id"="\d+"})
     *
     * @param int     $id
     * @param Request $request
     * @SerializerView(serializeNull=true)
     *
     * @throws \Exception
     *
     * @return View
     */
    public function putAction($id, Request $request)
    {
        $this->checkExposed(__METHOD__);
        $this->denyAccessUnlessGranted(
            PermissionGroupVoter::MODIFY,
            $this->getPermissionGroupEntityContext($id, $request)
        );

        /** @var ContentTemplate $model */
        $model = $this->findEntity($id, $request);
        $this->handleAttachments($model, $request);

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

    /**
     * @param ContentTemplate $contentTemplate
     * @param Request         $request
     *
     * @throws \Exception
     */
    protected function handleAttachments(ContentTemplate $contentTemplate, Request $request)
    {
        $em      = $this->getManager();
        $blobIds = $request->get('attach', []);

        if ($contentTemplate->getId()) {
            foreach ($contentTemplate->getAttachments() as $k => $attachment) {
                $blobId = $attachment->getBlob()->getId();

                if (!in_array($blobId, $blobIds, false)) {
                    $contentTemplate->getAttachments()->remove($k);
                    $em->remove($attachment);
                } else {
                    $key = array_search($blobId, $blobIds, false);
                    if ($key !== false) {
                        unset($blobIds[$key]);
                    }
                }
            }
        }

        foreach ($blobIds as $blob_id) {
            $blob = $em->getRepository('DeskPRO:Blob')->find($blob_id);
            if ($blob) {
                $attach = new ContentTemplateAttachment();
                $attach
                    ->setPerson($this->getUser())
                    ->setBlob($blob->setIsTemp(false)->setSourceRef('content_template.'.$contentTemplate->getId()));
                $em->persist($attach);
                $contentTemplate->addAttachment($attach);
            }
        }
    }
}
