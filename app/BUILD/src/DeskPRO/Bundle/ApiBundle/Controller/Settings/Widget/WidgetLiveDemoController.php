<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings\Widget;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\Currency;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Widget\WidgetLiveDemoState;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Widget\WidgetPeopleDemoState;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatMessages;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Data\ContentTypes;

/**
 * Class WidgetLiveDemoController.
 *
 * @ApiModes("all")
 * @Rest\Route("/widget/live_demo")
 */
class WidgetLiveDemoController extends BaseController
{
    /**
     * Sample state for widget preview.
     *
     * @ApiDoc(
     *     section="Widget sample online agents",
     *     resourceDescription="Operations about widget setup",
     *     description="get widget sample online agent",
     *     statusCodes={
     *         200="Returned if request was successful",
     *     },
     *
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\WidgetLiveDemoState"
     *)
     *
     * @Rest\Get("/sample_state")
     * @SerializerView(mapping={
     *     "Application\DeskPRO\Entity\Person": "DeskPRO\Bundle\AppBundle\Serializer\Model\Person\WidgetPerson"
     * })
     *
     * @return View
     */
    public function getSampleStateAction()
    {
        // get real people or create fake ones if less than required count exist
        $people   = [];
        $personId = 1;
        foreach (['agent', 'user'] as $role) {
            $isAgent = $role === 'agent';

            $count = $isAgent ? WidgetPeopleDemoState::AGENTS_COUNT : WidgetPeopleDemoState::USERS_COUNT;
            $group = $this->getRepository(Person::class)->findBy(['is_agent' => $isAgent], null, $count);

            // if we have less real people than required to create preview
            // then we should add fake ones to the list
            if (count($group) < $count) {
                $fakeCount = $count - count($group);
                for ($i = 0; $i < $fakeCount; ++$i) {
                    $group[] = $this->createFakePerson(true);
                }
            }

            // override people ids
            foreach ($group as $person) {
                $property = new \ReflectionProperty(Person::class, 'id');
                $property->setAccessible(true);
                $property->setValue($person, $personId++);
            }

            $people[$role] = $group;
        }

        // create sample chat state
        $chat = new ChatConversation();
        $chat->setPerson($people['user'][0]);
        $chat->setAgent($people['agent'][0]);

        $property = new \ReflectionProperty(ChatConversation::class, 'id');
        $property->setAccessible(true);
        $property->setValue($chat, 1);

        $chat->addMessage(UserChatMessages::createSysMessage(UserChatEvent::STARTED, new UserChatEvent($chat)));
        $chat->addMessage(UserChatMessages::createSysMessage(UserChatEvent::ASSIGNED, new UserChatEvent($chat, [
            'name' => $chat->getAgent()->getDisplayName(),
        ])));
        $chat->addMessage(UserChatMessages::createUserTextMessage($chat, 'Sample question'));
        $chat->addMessage(UserChatMessages::createAgentTextMessage($chat, 'Sample answer'));

        $departments = $this->getRepository(Department::class)->findBy(['is_chat_enabled' => true]);
        $currencies  = $this->getRepository(Currency::class)->findAll();

        return new View($this->wrap(new WidgetLiveDemoState($people['agent'], $people['user'], $chat, $departments, $currencies)));
    }

    /**
     * @param bool $isAgent
     *
     * @return Person
     */
    private function createFakePerson($isAgent)
    {
        static $blobId = 1;

        $faker  = $this->get('dp.fixtures.faker');
        $person = new Person();
        $person
            ->setIsAgent($isAgent)
            ->setFirstName($faker->firstName)
            ->setLastName($faker->lastName)
            ->setPictureBlob($this->getOrCreateFakeAvatarBlob($blobId++))
        ;

        $person->setEmail($faker->freeEmail);

        return $person;
    }

    /**
     * @param int $blobId
     *
     * @return Blob
     */
    private function getOrCreateFakeAvatarBlob($blobId)
    {
        $blob = $this->getRepository(Blob::class)->findOneBy([
            'sys_name' => WidgetPeopleDemoState::PERSON_AVATAR_SYS_PREFIX.$blobId,
        ]);

        if (!$blob) {
            $avatar = $this->container->get('dp.fixtures.people_avatar_reader')->next();
            $blob   = $this->container->get('deskpro.blob_storage')->createBlobRecordFromFile(
                $avatar->getRealPath(),
                $avatar->getFilename(),
                ContentTypes::getContentTypeFromFilename($avatar->getFilename())
            );

            $blob->setSysName(WidgetPeopleDemoState::PERSON_AVATAR_SYS_PREFIX.$blobId);
            $this->getManager()->persist($blob);
            $this->getManager()->flush();
        }

        return $blob;
    }
}
