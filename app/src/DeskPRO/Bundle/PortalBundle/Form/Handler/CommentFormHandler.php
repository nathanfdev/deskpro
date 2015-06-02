<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\PortalBundle\Form\Handler;


use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\PortalBundle\Person\LoginRequiredException;
use DeskPRO\Bundle\PortalBundle\Person\PersonFactory;
use DeskPRO\Bundle\PortalBundle\SavedForm\FormSaver;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class CommentFormHandler
{
    /**
     * @var FormSaver
     */
    private $saver;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var PersonFactory
     */
    private $person_factory;

    /**
     * @var FormFactory
     */
    private $form_factory;

    /**
     * @var TokenStorage
     */
    private $token_storage;

    public function __construct(
        FormSaver $saver,
        EntityManager $em,
        PersonFactory $person_factory,
        FormFactory $form_factory,
        TokenStorage $token_storage
    )
    {
        $this->saver = $saver;
        $this->em = $em;
        $this->person_factory = $person_factory;
        $this->form_factory = $form_factory;
        $this->token_storage = $token_storage;
    }

    public function handle(FormInterface $form, Request $request, ContentAbstract $content, CommentAbstract $comment)
    {
        $comment->setObject($content);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $person = $comment->getPerson();
            if ($person instanceof PersonGuest) {
                // we are dealing with a guest...
                try {
                    $e = new PersonEmail();
                    $e->setEmail($comment->email);
                    $person->setPrimaryEmail($e);
                    $person->setName($comment->name);
                    // turn the guest into a contact or a person
                    $person = $this->person_factory->createPersonFromGuest($person);
                    $comment->setPerson($person);
                } catch (LoginRequiredException $e) {
                    // oops! A login is required. This "guest" cannot post a comment until logged in.
                    $person = $e->getPerson();

                    // return the redirect response
                    return $this->saver->saveFormForPerson($person, $form, $request);
                }
            }
            $content->addComment($comment);
            $this->em->persist($comment);
            $this->em->flush(array($comment, $content));

            return true;
        }

        return false;
    }

    public function createForm(CommentAbstract $comment)
    {
        if (!$comment->getPerson()) {
            $comment->setPerson($this->getUser());
        }

        return $this->form_factory->create(
            'comment',
            $comment,
            array(
                'person' => $this->getUser(),
                'allow_extra_fields' => true
            )
        );
    }

    /**
     * @return \Application\DeskPRO\Entity\Person|null
     */
    protected function getUser()
    {
        if (null === $token = $this->token_storage->getToken()) {
            return new PersonGuest();
        }

        if (!is_object($user = $token->getUser())) {
            return new PersonGuest();
        }

        return $user;
    }
}
