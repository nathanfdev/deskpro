<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Community\CommunityForumEdit;
use Application\DeskPRO\Community\Form\Type\CommunityForumType;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\CommunityForumToStatus;
use Application\DeskPRO\Exception\ValidationException;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\AgentPermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\IconProperty;
use Orb\Util\Arrays;

/**
 * @ApiModes("all")
 */
class CommunityForumsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new AgentPermission(), 'listAction');

        return $multi;
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction()
    {
        $communityForums = $this->container->getSystemService('community_forums');

        return $this->createApiResponse(
            [
                'types' => $this->getApiData(Arrays::flatten($communityForums->getAll())),
            ]
        );
    }

//##################################################################################################################
    //###################################################################################################################

    public function getAction($id)
    {
        $communityForums = $this->container->getSystemService('community_forums');
        $communityForum  = $communityForums->getById($id);

        if (!$communityForum) {
            throw $this->createNotFoundException();
        }

        $returnedData               = $this->getApiData($communityForum, true);
        $returnedData['usergroups'] = $communityForums->getNonAgentUserGroups($communityForum);

        return $this->createApiResponse(
            [
                'community_forum' => $returnedData,
            ]
        );
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    public function saveAction($id)
    {
        $communityForums = $this->container->getSystemService('community_forums');

        if ($id) {
            $communityForum = $communityForums->getById($id);

            if (!$communityForum) {
                throw $this->createNotFoundException();
            }
        } else {
            $communityForum = $communityForums->createNew();
        }

        $postData = $this->in->getAll('post');
        $this->em->beginTransaction();

        // it's much much easier to delete all communtiyForum-2-status relation, cause it have no surrogate id
        $all = $this->em->getRepository(CommunityForumToStatus::class)->findBy(['forum' => $communityForum]);
        foreach ($all as $junction) {
            $this->em->remove($junction);
        }
        $this->em->flush();

        $communityForumEdit = new CommunityForumEdit($communityForum);
        $form               = $this->createForm(CommunityForumType::class, $communityForumEdit, ['cascade_validation' => true, 'forum' => $communityForum]);
        $form->submit($this->deleteExtraDataFromRequest($form, $postData, 'community_forum'), false);

        if ($form->isValid()) {
            if ($this->in->getString('community_forum.icon_property.urn')) {
                $icon = new IconProperty();
                $icon->setUrn($this->in->getString('community_forum.icon_property.urn'));
                if ($icon->getUrnNs() === IconProperty::$blobNs) {
                    $authId = $icon->getUrnPath();
                    $blob   = $this->em->getRepository(Blob::class)->getByAuthId($authId);
                    if ($blob) {
                        $rawFile = $this->get('blob.storage')->copyBlobRecordToString($blob);
                        $blob    = $this->get('blob.storage')->createBlobRecordFromString(
                            $rawFile,
                            $blob->getFilename(),
                            $blob->getContentType()
                        );
                        $this->em->persist($blob);
                        $this->em->persist($icon);
                        $icon->setBlob($blob);
                    }
                }
                $options = [];
                if ($this->in->getString('community_forum.icon_property.options.style')) {
                    $options['style'] = $this->in->getString('community_forum.icon_property.options.style');
                }
                if ($this->in->getString('community_forum.icon_property.options.color')) {
                    $options['color'] = $this->in->getString('community_forum.icon_property.options.color');
                }
                if ($options) {
                    $icon->setOptions($options);
                }
                $this->em->persist($icon);
                if ($communityForumEdit->community_forum->getIcon()) {
                    $this->em->remove($communityForumEdit->community_forum->getIcon());
                }
                $communityForumEdit->community_forum->setIcon($icon);
            }

            $communityForumEdit->save($this->em);
            $this->em->commit();
        } else {
            $this->em->rollback();

            return $this->createApiValidationErrorResponse($this->container->getValidator()->validate($communityForum));
        }

        return $this->createApiResponse([
            'success' => true,
            'id'      => $communityForum->getId(),
            'brand'   => $communityForum->getBrand() ? $communityForum->getBrand()->getId() : null,
        ]);
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    public function removeAction($id)
    {
        $communityForums = $this->container->getSystemService('community_forums');
        $communityForum  = $communityForums->getById($id);

        if (!$communityForum) {
            throw $this->createNotFoundException();
        }

        $move_to                 = $this->in->getUint('move_to');
        $move_to_community_forum = $communityForums->getById($move_to);

        if (!$move_to_community_forum) {
            throw ValidationException::create(
                'community_forum.remove.move_community_forums',
                'You must select a community forum to move existing community topics into'
            );
        }

        if ($move_to_community_forum->getId() == $communityForum->getId()) {
            throw ValidationException::create(
                'community_forum.remove.move_community_forums',
                'You must choose a different community forums'
            );
        }

        $old_id = $communityForum->getId();

        $this->db->beginTransaction();

        try {
            $this->db->executeUpdate(
                'UPDATE community_topics SET forum_id = ? WHERE forum_id = ?',
                [$move_to, $old_id]
            );

            $this->em->remove($communityForum);
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse(['old_id' => $old_id]);
    }

    //###################################################################################################################
    // save-display-order
    //###################################################################################################################

    public function saveDisplayOrderAction()
    {
        $display_orders = $this->in->getArrayOfUInts('display_orders');

        $communityForums = $this->container->getSystemService('community_forums');
        $communityForums->updateDisplayOrders($display_orders);

        return $this->createSuccessResponse();
    }
}
