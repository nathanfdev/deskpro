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

namespace DeskPRO\Bundle\SendmailBundle\Factory;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Task;
use DateTime;
use Doctrine\ORM\EntityManager;
use Orb\Util\Numbers;
use ReflectionClass;
use ReflectionParameter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class PreviewFakeDataFactory
{
    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * PreviewFakeDataFactory constructor.
     *
     * @param RouterInterface $router
     * @param EntityManager   $manager
     */
    public function __construct(RouterInterface $router, EntityManager $manager)
    {
        $this->router  = $router;
        $this->manager = $manager;
    }

    /**
     * @param AgentViewModelFactory|UserViewModelFactory $factory
     * @param string                                     $action
     * @param Request                                    $request
     *
     * @return array
     */
    public function getArguments($factory, $action, $request)
    {
        $arguments = [];

        $reflection = new ReflectionClass($factory);
        $parameters = $reflection->getMethod($action)->getParameters();
        $arguments  = array_map(function ($parameter) use ($request) {
            /** @var ReflectionParameter $parameter */
            $parameterClass = $parameter->getClass();
            if ($parameterClass) {
                $className = $parameterClass->getName();
                if ($className === Request::class) {
                    return $request;
                }
                if ($className === DateTime::class) {
                    return new DateTime();
                }
                if ($className === CommentAbstract::class) {
                    $className = ArticleComment::class;
                }

                return $this->manager->getRepository($className)->findOneBy([]);
            } else {
                switch ($parameter->getName()) {
                    case 'resetUrl':
                        return $this->router->generate(
                            'portal_set_password_process',
                            ['code' => '123456'],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                    case 'verifyUrl':
                        return $this->router->generate(
                            'portal_validation',
                            [
                                'type'      => 'test',
                                'auth_code' => '123456',
                            ],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                    case 'accessCode':
                        return '123456';
                    case 'convoMessages':
                        return $this->manager->getRepository(ChatMessage::class)->findBy([]);
                    case 'newArticles':
                    case 'updatedArticles':
                        return $this->manager->getRepository(Article::class)->findBy([], [], 10);
                    case 'newDownloads':
                    case 'updatedDownloads':
                        return $this->manager->getRepository(Download::class)->findBy([], [], 10);
                    case 'newNews':
                    case 'updatedNews':
                        return $this->manager->getRepository(News::class)->findBy([], [], 10);
                    case 'updatedFeedback':
                        return $this->manager->getRepository(Feedback::class)->findBy([], [], 10);
                    case 'task':
                        return $this->manager->getRepository(Task::class)->findBy([], [], 10);
                    case 'newPassword':
                        return 'newP@ssword';
                    case 'reason':
                        return 'Disapproved reason';
                    case 'message':
                        return 'Example message';
                    case 'email':
                        return 'email@example.com';
                    case 'name':
                        return 'First Name Last Name';
                    case 'subject':
                        return 'Example Subject';
                    case 'agentMessage':
                        return 'Here\'s my forwarded ticket';
                    case 'maxSize':
                        return Numbers::filesizeDisplay(20971520);
                    case 'expireDate':
                        $now = new DateTime();

                        return $now->add(new \DateInterval('P1M'));
                    default:
                        return '';
                }
            }
        }, $parameters);

        return $arguments;
    }
}
