<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Request;

use Application\DeskPRO\Entity\ChatBlock;
use Application\DeskPRO\Entity\ChatConversation;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ApiChatConversationConverter.
 */
class ApiChatConversationConverter implements ParamConverterInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $authId = $request->attributes->get('id');
        if (!is_string($authId)) {
            throw new NotFoundHttpException('Chat not found');
        }

        $parsed   = explode(':', $authId);
        $chatId   = $parsed[0];
        $authCode = isset($parsed[1]) ? $parsed[1] : '';

        // get chat
        $chat = $this->em->getRepository(ChatConversation::class)->find($chatId);
        if (!$chat) {
            throw new NotFoundHttpException('Chat not found');
        }

        // verify chat
        if (!$chat->getSession() || $chat->getSession()->getAuth() !== $authCode) {
            throw new AccessDeniedHttpException('Wrong session code');
        }

        /** @var \Application\DeskPRO\EntityRepository\ChatBlock $repo */
        $repo  = $this->em->getRepository(ChatBlock::class);
        $block = $repo->getBlockForVisitor($chat->getSession()->getVisitorId(), $chat->getSession()->getIpAddress());

        if ($block) {
            throw new AccessDeniedHttpException('Banned');
        }

        $request->attributes->set('conversation', $chat);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'portal_api_chat';
    }
}
