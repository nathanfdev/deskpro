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

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\Searcher\PersonSearch;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\SuperKeyPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Numbers;
use Orb\Util\Util;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @ApiModes("all")
 */
class PersonController extends AbstractController implements ProtectedControllerInterface
{
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new SuperKeyPermission(), 'authLoginAction');

        return $multi;
    }

    /**
     * @return Response
     */
    public function searchAction()
    {
        $search_map = [
            'address'         => PersonSearch::TERM_CONTACT_ADDRESS,
            'agent_team_id'   => PersonSearch::TERM_AGENT_TEAM,
            'alpha'           => PersonSearch::TERM_ALPHA,
            'email'           => PersonSearch::TERM_EMAIL,
            'email_domain'    => PersonSearch::TERM_EMAIL_DOMAIN,
            'im'              => PersonSearch::TERM_CONTACT_IM,
            'label'           => PersonSearch::TERM_LABEL,
            'name'            => PersonSearch::TERM_NAME,
            'language_id'     => PersonSearch::TERM_LANGUAGE,
            'organization_id' => PersonSearch::TERM_ORGANIZATION,
            'phone'           => PersonSearch::TERM_CONTACT_PHONE,
            'usergroup_id'    => PersonSearch::TERM_USERGROUP,
        ];

        $terms = [];

        foreach ($search_map as $input => $search_key) {
            $value = $this->in->getCleanValueArray($input, 'raw', 'discard');
            if ($value) {
                $terms[] = ['type' => $search_key, 'op' => 'contains', 'options' => $value];
            }
        }

        if ($this->in->checkIsset('is_agent')) {
            if ($this->in->getBool('is_agent')) {
                $terms[] = ['type' => PersonSearch::TERM_AGENT_MODE, 'op' => 'is', 'options' => 1];
            } else {
                $terms[] = ['type' => PersonSearch::TERM_USER_MODE, 'op' => 'is', 'options' => 1];
            }
        }

        $date_created_start = $this->in->getUint('date_created_start');
        $date_created_end   = $this->in->getUint('date_created_end');
        if ($date_created_end) {
            $terms[] = ['type' => PersonSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => [
                'date1' => $date_created_start,
                'date2' => $date_created_end,
            ]];
        } elseif ($date_created_start) {
            $terms[] = ['type' => PersonSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => [
                'date1' => $date_created_start,
            ]];
        }

        foreach ($this->container->getSystemService('person_fields_manager')->getFields() as $field) {
            if ($this->in->checkIsset('field.'.$field->getId())) {
                $in_val = $this->in->getString('field.'.$field->getId());
                if ($in_val) {
                    $terms[] = ['type' => 'person_field['.$field->getId().']', 'op' => 'is', 'options' => ['value' => $in_val]];
                }
            }
        }

        if ($this->in->checkIsset('order')) {
            $order_by = $this->in->getString('order');
        } else {
            $order_by = $this->person->getPref('agent.ui.people-filter-order-by.0');
            if (!$order_by) {
                $order_by = 'people.id:asc';
            }
        }

        $extra = [];
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('person', $terms, $extra, $this->in->getUint('cache_id'), new PersonSearch());

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $person_ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($person_ids, $page, $per_page);
        $people   = App::getEntityRepository('DeskPRO:Person')->getByIds($page_ids, true);

        return $this->createApiResponse([
            'page'     => $page,
            'per_page' => $per_page,
            'total'    => count($person_ids),
            'cache_id' => $result_cache->id,
            'people'   => $this->getApiData($people),
        ]);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function newPersonAction()
    {
        if (!$this->person->hasPerm('agent_people.create')) {
            throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
        }

        $person = new Person();
        $errors = [];

        if ($this->in->checkIsset('name')) {
            $person->name = $this->in->getString('name');
        }
        if ($this->in->checkIsset('first_name')) {
            $person->first_name = $this->in->getString('first_name');
        }
        if ($this->in->checkIsset('last_name')) {
            $person->last_name = $this->in->getString('last_name');
        }

        $updates = $this->_setBasicPersonDetailsFromInput($person);

        foreach ($this->in->getArrayValue('contact_data') as $contact) {
            $contact_type = isset($contact['type']) ? $contact['type'] : false;
            $data         = (isset($contact['data']) && is_array($contact['data'])) ? $contact['data'] : false;

            if (!$contact_type || !$data) {
                continue;
            }

            $data['comment'] = isset($contact['comment']) ? $contact['comment'] : '';

            $contact_data               = new \Application\DeskPRO\Entity\PersonContactData();
            $contact_data->contact_type = $contact_type;
            try {
                $contact_data->applyFormData($data);
            } catch (\InvalidArgumentException $e) {
                // invalid type
                continue;
            }

            $all_empty = true;
            for ($i = 1; $i <= 10; ++$i) {
                if ($contact_data->{'field_'.$i}) {
                    $all_empty = false;
                    break;
                }
            }

            if (!$all_empty) {
                $person->addContactData($contact_data);
            }
        }

        foreach ($this->in->getCleanValueArray('group_id', 'int') as $ug_id) {
            $ug = $this->em->find('DeskPRO:Usergroup', $ug_id);
            if ($ug && !$ug->is_agent_group && !$ug->sys_name) {
                $person->usergroups->add($ug);
            }
        }

        $email = $this->in->getString('email');
        if (!$email || !\Orb\Validator\StringEmail::isValueValid($email) || App::$container->getEmailAccountManager()->findAccountForEmailAddress($email)) {
            $errors['email'] = ['required_field.email', 'email is empty or invalid'];
        } else {
            $check_exists = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);
            if ($check_exists) {
                $errors['email'] = ['invalid_argument.email', 'email already exists'];
            } else {
                $person->setEmail($email);
            }
        }

        foreach ($this->in->getCleanValueArray('secondary_email', 'string') as $secondary_email) {
            if (!$secondary_email || !\Orb\Validator\StringEmail::isValueValid($secondary_email) || App::$container->getEmailAccountManager()->findAccountForEmailAddress($secondary_email)) {
                $errors['secondary_email'] = ['invalid_argument.secondary_email', 'secondary_email is empty or invalid'];
            } else {
                $check_exists = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($secondary_email);
                if ($check_exists) {
                    $errors['secondary_email'] = ['invalid_argument.secondary_email', 'secondary_email already exists'];
                } else {
                    $person->addEmailAddressString($secondary_email);
                }
            }
        }

        if ($errors) {
            return $this->createApiMultipleErrorResponse($errors);
        }

        $this->db->beginTransaction();

        try {
            if ($updates['new_org']) {
                $this->em->persist($updates['new_org']);
            }

            $this->em->persist($person);
            $this->em->flush();

            $field_manager      = $this->container->getSystemService('person_fields_manager');
            $post_custom_fields = $this->getCustomFieldInput();
            if (!empty($post_custom_fields)) {
                $field_manager->saveFormToObject($post_custom_fields, $person, true);
                $this->em->flush();
            }

            $labels = $this->in->getCleanValueArray('label', 'string', 'discard');
            if ($labels) {
                $person->getLabelManager()->setLabelsArray($labels, $this->em);
                $this->em->flush();
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        if ($this->in->getBool('send_email')) {
            $message = App::getMailer()->createMessage();
            $message->setToPerson($person);

            $tpl = 'DeskPRO:emails_user:register-welcome.html.twig';
            if ($this->in->getBool('via_agent')) {
                $tpl = 'DeskPRO:emails_user:register-welcome-byagent.html.twig';
            }
            $message->setTemplate($tpl, [
                'person' => $person,
            ]);
            App::getMailer()->send($message);
        }

        return $this->createApiCreateResponse(
            ['id' => $person->id],
            $this->generateUrl(
                'api_people_person',
                ['person_id' => $person->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @return Response
     */
    public function getPersonAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id);

        return $this->createApiResponse(['person' => $person->toApiData()]);
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postPersonAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');

        $errors = [];

        if ($this->in->checkIsset('name')) {
            $person->name = $this->in->getString('name');
        }
        if ($this->in->checkIsset('first_name')) {
            $person->first_name = $this->in->getString('first_name');
        }
        if ($this->in->checkIsset('last_name')) {
            $person->last_name = $this->in->getString('last_name');
        }

        $updates = $this->_setBasicPersonDetailsFromInput($person);

        if ($this->in->checkIsset('primary_email') && $this->person->hasPerm('agent_people.manage_emails')) {
            $email = $this->in->getString('primary_email');

            $check_exists = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);
            if ($check_exists) {
                if ($check_exists->id != $person->id) {
                    $errors['primary_email'] = ['invalid_argument.primary_email', 'email already exists'];
                }
            } else {
                $person->setEmail($email);
            }
        }

        if ($errors) {
            return $this->createApiMultipleErrorResponse($errors);
        }

        $this->db->beginTransaction();

        try {
            if ($updates['new_org']) {
                $this->em->persist($updates['new_org']);
            }
            $this->em->persist($person);

            $field_manager      = $this->container->getSystemService('person_fields_manager');
            $post_custom_fields = $this->getCustomFieldInput();
            if (!empty($post_custom_fields)) {
                $field_manager->saveFormToObject($post_custom_fields, $person, true);
            }
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    protected function _setBasicPersonDetailsFromInput(Person $person)
    {
        $org = null;

        if ($this->in->checkIsset('organization')) {
            $organization = $this->in->getString('organization');
            if ($organization !== '') {
                $org = $this->em->getRepository('DeskPRO:Organization')->getByName($organization);

                if (!$org) {
                    $org       = new Organization();
                    $org->name = $organization;
                }

                $person->organization = $org;
            } else {
                $person->organization          = null;
                $person->organization_position = '';
            }
        } elseif ($this->in->checkIsset('organization_id')) {
            $organization_id = $this->in->getUint('organization_id');
            if ($organization_id) {
                $org = $this->em->getRepository('DeskPRO:Organization')->find($organization_id);
            }

            if ($org) {
                $person->organization = $org;
            } else {
                $person->organization          = null;
                $person->organization_position = '';
            }
        }

        if ($this->in->checkIsset('password')) {
            $person->setPassword($this->in->getString('password'));
        }

        if ($this->in->checkIsset('organization_position') && $person->organization) {
            $person->organization_position = $this->in->getString('organization_position');
        }

        if ($this->in->checkIsset('timezone') && in_array($this->in->getString('timezone'), \DateTimeZone::listIdentifiers())) {
            $person->timezone = $this->in->getString('timezone');
        }

        $bulk_set = [
            'summary'               => 'String',
            'disable_autoresponses' => 'Bool',
            'is_disabled'           => 'Bool',
        ];
        foreach ($bulk_set as $input => $type) {
            if ($this->in->checkIsset($input)) {
                $person->$input = $this->in->{'get'.$type}($input);
            }
        }

        return [
            'new_org' => $org,
        ];
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deletePersonAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id, 'delete');

        if ($person->is_agent) {
            return $this->createApiErrorResponse('no_delete_agents', 'You cannot delete agents. There is a separate agents resource that you should use instead.');
        }
        if ($person->id == $this->person->id) {
            return $this->createApiErrorResponse('no_delete_self', 'You cannot delete yourself');
        }

        $edit_manager = $this->container->getSystemService('person_edit_manager');
        $edit_manager->setPersonContext($this->person);
        $edit_manager->deleteUser($person);

        return $this->createSuccessResponse();
    }

    /**
     * @param int $person_id
     * @param int $other_person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function mergePersonAction($person_id, $other_person_id)
    {
        $person       = $this->_getPersonOr404($person_id);
        $other_person = $this->_getPersonOr404($other_person_id);

        if (!$person || !$other_person) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        if (!$this->person->hasPerm('agent_people.merge') || !$this->isPersonEditable($person)) {
            throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
        }

        if (!$this->person->hasPerm('agent_people.merge') || !$this->isPersonEditable($other_person)) {
            throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
        }

        $merge = new \Application\DeskPRO\People\PersonMerge\PersonMerge($this->person, $person, $other_person);
        $merge->merge();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonPictureAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id);

        $size = $this->in->getUint('size');
        if (!$size) {
            $size = 80;
        }

        return $this->createApiResponse([
            'has_picture' => $person->hasPicture(),
            'picture_url' => $person->getPictureUrl($size),
            'size'        => $size,
        ]);
    }

    /**
     * @param int $person_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function postPersonPictureAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');

        $file   = $this->request->files->get('file');
        $accept = $this->container->getAttachmentAccepter();

        if ($file) {
            $error = $accept->getError($file, 'agent');
            if (!$error) {
                $set = new \Application\DeskPRO\Attachments\RestrictionSet();
                $set->setAllowedExts(['gif', 'png', 'jpg', 'jpeg']);
                $accept->addRestrictionSet('only_images', $set);
                $error = $accept->getError($file, 'only_images');
            }
            if ($error) {
                $message = $this->container->getTranslator()->phrase('agent.general.attach_error_'.$error['error_code'], $error);

                return $this->createApiErrorResponse($error['error_code'], $message);
            }

            $blob = $accept->accept($file);
        } else {
            $blob_id = $this->in->getUint('blob_id');
            $blob    = $this->em->find('DeskPRO:Blob', $blob_id);
            if (!$blob) {
                return $this->createApiErrorResponse('invalid_argument.blob_id', 'blob_id not found');
            }
        }

        $blobCurr = $person->getPictureBlob();
        if ( $blobCurr && $blobCurr->getId() !== $blob->getId()) {
            $this->container->getBlobStorage()->deleteBlobRecord($blobCurr);
        }

        $person->setPictureBlob($blob);
        $this->em->persist($person);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deletePersonPictureAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');

        if ($person->getPictureBlob()) {
            $this->container->getBlobStorage()->deleteBlobRecord($person->getPictureBlob());
        }
        $person->setPictureBlob(null);
        $this->em->persist($person);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonEmailsAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id);

        return $this->createApiResponse(['emails' => $this->getApiData($person->emails)]);
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postPersonEmailsAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');

        if (!$this->person->hasPerm('agent_people.manage_emails')) {
            throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
        }

        $email = $this->in->getString('email');

        if (!$email) {
            return $this->createApiErrorResponse('required_field.email', 'email missing');
        }

        if (!\Orb\Validator\StringEmail::isValueValid($email) || App::$container->getEmailAccountManager()->findAccountForEmailAddress($email)) {
            return $this->createApiErrorResponse('invalid_argument.email', 'invalid email');
        }

        $check = $this->em->getRepository('DeskPRO:PersonEmail')->getEmail($email);
        if ($check) {
            if ($check->person->id == $person->id) {
                return $this->createApiErrorResponse('invalid_argument.email', 'email in use by self');
            } else {
                return $this->createApiErrorResponse('invalid_argument.email', 'email in use');
            }
        }

        $comment = $this->in->getString('comment');

        $email_rec          = $person->addEmailAddressString($email);
        $email_rec->comment = $comment;
        $this->em->persist($email_rec);

        if ($this->in->getBool('set_primary')) {
            $person->primary_email = $email_rec;
            $this->em->persist($person);
        }

        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $email_rec->id],
            $this->generateUrl(
                'api_people_person_email',
                ['person_id' => $person->id, 'email_id' => $email_rec->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $person_id
     * @param int $email_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonEmailAction($person_id, $email_id)
    {
        $person = $this->_getPersonOr404($person_id);
        $email  = false;

        foreach ($person->emails as $test_email) {
            if ($test_email->id == $email_id) {
                $email = $test_email;
                break;
            }
        }

        if (!$email) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        return $this->createApiResponse(['email' => $this->getApiData($email)]);
    }

    /**
     * @param int $person_id
     * @param int $email_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postPersonEmailAction($person_id, $email_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');
        $email  = false;

        foreach ($person->emails as $test_email) {
            if ($test_email->id == $email_id) {
                $email = $test_email;
                break;
            }
        }

        if (!$email) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        if (!$this->person->hasPerm('agent_people.manage_emails')) {
            throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
        }

        if ($this->in->checkIsset('comment')) {
            $email->comment = $this->in->getString('comment');
            $this->em->persist($email);
            $this->em->flush();
        }

        if ($this->in->getBool('set_primary')) {
            $person->primary_email = $email;
            $this->em->persist($person);
            $this->em->flush();
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $person_id
     * @param int $email_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deletePersonEmailAction($person_id, $email_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');
        $email  = false;

        foreach ($person->emails as $test_email) {
            if ($test_email->id == $email_id) {
                $email = $test_email;
                break;
            }
        }

        if (!$email) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        if (!$this->person->hasPerm('agent_people.manage_emails')) {
            throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
        }

        if (count($person->emails) == 1) {
            return $this->createApiErrorResponse('required_field', 'cannot remove the last email');
        }

        $person->emails->removeElement($email);

        $is_primary = ($person->primary_email && $email->id == $person->primary_email->id);

        $this->em->remove($email);

        if ($is_primary) {
            foreach ($person->emails as $new_primary) {
                $person->primary_email = $new_primary;
                $this->em->persist($person);
                break;
            }
        }

        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     * @throws \File_IMC_Exception
     *
     * @return Response
     */
    public function getPersonVcardAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id);

        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->headers->set('Content-Type', 'text/vcf');

        if ($person->getName()) {
            $filename = $person->getName();
        } else {
            $filename = $person->getEmailAddress();
        }

        $filename = str_replace(' ', '_', $filename);
        $filename = preg_replace('[^a-zA-Z0-9_.@-]', '', $filename);

        if (strlen($filename) == 0) {
            $filename = 'Unknown_'.$person->id;
        }

        if (strlen($filename) > 128) {
            $filename = substr($filename, 0, 128);
        }

        $response->headers->set('Content-Disposition', 'attachment; filename='.$filename.'.vcf');
        $vcard = \File_IMC::build('vCard');

        $vcard->setFormattedName($person->name);
        $vcard->setName($person->last_name, $person->first_name, '', '', '');

        if ($person->organization) {
            $vcard->addOrganization($person->organization->name);
        }

        if (!empty($person['organization_position'])) {
            $vcard->setTitle($person['organization_position']);
        }

        foreach ($person->emails as $email) {
            $vcard->addEmail($email->email);
        }

        foreach ($person->contact_data as $c_data) {
            $data = $c_data->getTemplateVars();
            switch ($c_data['contact_type']) {
                case 'phone':
                    if (empty($data['number'])) {
                        break;
                    }

                    $tel = '';

                    if (!empty($data['country_calling_code'])) {
                        $tel .= '+'.$data['country_calling_code'].'-';
                    }

                    $tel .= $data['number'];

                    $vcard->addTelephone($tel);
                    break;

                case 'website':
                    $vcard->setURL($data['url']);
                    break;

                case 'address':
                    $vcard->addAddress(
                        '',
                        '',
                        $data['address'],
                        $data['city'],
                        $data['state'],
                        $data['zip'],
                        $data['country']
                    );
                    break;
            }
        }

        $response->setContent($vcard->fetch());

        return $response;
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonActivityStreamAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id);

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }

        $per_page = 25;
        $offset   = $per_page * ($page - 1);

        $activity = $this->em->getRepository('DeskPRO:PersonActivity')->getForPerson($person, $per_page, $offset);
        $total    = $this->em->getRepository('DeskPRO:PersonActivity')->countForPerson($person);

        return $this->createApiResponse([
            'page'     => $page,
            'per_page' => $per_page,
            'total'    => $total,
            'activity' => $this->getApiData($activity),
        ]);
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonTicketsAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id);

        $terms = [
            [
                'type'    => \Application\DeskPRO\Searcher\TicketSearch::TERM_PERSON,
                'op'      => 'contains',
                'options' => [$person->id],
            ],
        ];

        if ($this->in->checkIsset('order')) {
            $order_by = $this->in->getString('order');
        } else {
            $order_by = 'ticket.date_created:desc';
        }

        $extra = [];
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('ticket', $terms, $extra, $this->in->getUint('cache_id'), new \Application\DeskPRO\Searcher\TicketSearch());

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $person_ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($person_ids, $page, $per_page);
        $tickets  = App::getEntityRepository('DeskPRO:Ticket')->getByIds($page_ids, true);

        return $this->createApiResponse([
            'page'     => $page,
            'per_page' => $per_page,
            'total'    => count($person_ids),
            'cache_id' => $result_cache->id,
            'tickets'  => $this->getApiData($tickets),
        ]);
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonChatsAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id);

        $terms = [
            [
                'type'    => \Application\DeskPRO\Searcher\ChatConversationSearch::TERM_PERSON,
                'op'      => 'contains',
                'options' => [$person->id],
            ],
        ];

        $order_by = 'chat_conversations.id:desc';

        $extra = [];
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('chat', $terms, $extra, $this->in->getUint('cache_id'), new \Application\DeskPRO\Searcher\ChatConversationSearch());

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($ids, $page, $per_page);
        $chats    = App::getEntityRepository('DeskPRO:ChatConversation')->getByIds($page_ids, true);

        return $this->createApiResponse([
            'page'     => $page,
            'per_page' => $per_page,
            'total'    => count($ids),
            'cache_id' => $result_cache->id,
            'chats'    => $this->getApiData($chats),
        ]);
    }

    /**
     * @param int $person_id
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Exception
     *
     * @return Response
     */
    public function resetPasswordAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id, 'reset_password');

        $password = $this->in->getString('password');
        if (!$password) {
            return $this->createApiErrorResponse('required_field', 'password field is missing or empty');
        }

        if ($this->in->checkIsset('send_email')) {
            $send_email = $this->in->getBool('send_email');
        } else {
            $send_email = true;
        }

        $person->setPassword($password);
        $this->db->delete('sessions', ['person_id' => $person->id]);
        $this->em->persist($person);

        if ($send_email) {
            $message = $this->container->getMailer()->createMessage();
            $message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
            $message->setTemplate('DeskPRO:emails_user:agent-changed-password.html.twig', [
                'person' => $person,
            ]);
            $this->container->getMailer()->send($message);
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $person_id
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Exception
     *
     * @return Response
     */
    public function clearSessionAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id, 'reset_password');
        $this->db->delete('sessions', ['person_id' => $person->id]);

        return $this->createSuccessResponse();
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonNotesAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id);
        $notes  = $this->em->getRepository('DeskPRO:PersonNote')->getNotesForPerson($person);

        return $this->createApiResponse(['notes' => $this->getApiData($notes)]);
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postPersonNotesAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id, 'notes');

        $note_text = $this->in->getString('note');
        if (!$note_text) {
            return $this->createApiErrorResponse('required_field', 'note field is empty or missing');
        }

        $note           = new \Application\DeskPRO\Entity\PersonNote();
        $note['agent']  = $this->person;
        $note['person'] = $person;
        $note['note']   = $note_text;
        $person->addNote($note);

        $this->em->persist($note);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $note->id],
            $this->generateUrl(
                'api_people_person_notes',
                ['person_id' => $person->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonBillingChargesAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id);

        $per_page = 25;

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }

        $offset = ($page - 1) * $per_page;

        $person_charges       = $this->em->getRepository('DeskPRO:TicketCharge')->getChargesForPerson($person, $per_page, $offset);
        $person_charge_totals = $this->em->getRepository('DeskPRO:TicketCharge')->getTotalChargesForPerson($person);

        return $this->createApiResponse([
            'total_charge_time'   => $person_charge_totals['charge_time'],
            'total_charge_amount' => $person_charge_totals['charge'],
            'total'               => $person_charge_totals['count'],
            'per_page'            => $per_page,
            'page'                => $page,
            'charges'             => $this->getApiData($person_charges),
        ]);
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonContactDetailsAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id);

        return $this->createApiResponse(['details' => $this->getApiData($person->contact_data)]);
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postPersonContactDetailsAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');

        $type    = $this->in->getString('type');
        $data    = $this->in->getArrayValue('data');
        $comment = $this->in->getString('comment');

        if (!$type) {
            return $this->createApiErrorResponse('required_field.type', 'type is empty or missing');
        }
        if (!$data) {
            return $this->createApiErrorResponse('required_field.data', 'data is empty or missing');
        }

        if (in_array($type, ['fax', 'mobile', 'phone'])) {
            return $this->createApiErrorResponse('error', 'Please use "/people/{person_id}/phone_numbers" API.');
        }

        $data['comment'] = $comment;

        $contact_data               = new \Application\DeskPRO\Entity\PersonContactData();
        $contact_data->contact_type = $type;
        try {
            $contact_data->applyFormData($data);
        } catch (\InvalidArgumentException $e) {
            return $this->createApiErrorResponse('invalid_argument.type', 'type is invalid');
        }

        $all_empty = true;
        for ($i = 1; $i <= 10; ++$i) {
            if ($contact_data->{'field_'.$i}) {
                $all_empty = false;
                break;
            }
        }

        if ($all_empty) {
            return $this->createApiErrorResponse('invalid_argument.data', 'data contains invalid data');
        }

        $contact_data->person = $person;

        $this->em->persist($contact_data);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $contact_data->id],
            $this->generateUrl(
                'api_people_person_contact_detail',
                ['person_id' => $person->id, 'contact_id' => $contact_data->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $person_id
     * @param int $contact_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonContactDetailAction($person_id, $contact_id)
    {
        $person = $this->_getPersonOr404($person_id);

        foreach ($person->contact_data as $contact) {
            if ($contact->id == $contact_id) {
                return $this->createApiResponse(['exists' => true]);
            }
        }

        return $this->createApiResponse(['exists' => false]);
    }

    /**
     * @param int $person_id
     * @param int $contact_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deletePersonContactDetailAction($person_id, $contact_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');

        foreach ($person->contact_data as $contact) {
            if ($contact->id == $contact_id) {
                $this->em->remove($contact);
                $this->em->flush();

                return $this->createSuccessResponse();
            }
        }

        throw $this->createNotFoundException();
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonGroupsAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id);

        return $this->createApiResponse(['groups' => $this->getApiData($person->usergroups)]);
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postPersonGroupsAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');

        $group_id = $this->in->getUint('id');

        $match = $this->db->fetchColumn('
            SELECT id
            FROM usergroups
            WHERE id = ?
                AND sys_name IS NULL
                AND is_agent_group = 0
        ', [$group_id]);
        if (!$match) {
            return $this->createApiErrorResponse('required_field', 'id must be specified as a non-system group');
        }

        $exists = false;
        foreach ($person->usergroups as $group) {
            if ($group->id == $group_id) {
                $exists = true;
            }
        }

        if (!$exists) {
            $this->db->insert('person2usergroups', [
                'person_id'    => $person->id,
                'usergroup_id' => $group_id,
            ]);
        }

        return $this->createApiCreateResponse(
            ['id' => $group_id],
            $this->generateUrl(
                'api_people_person_group',
                ['person_id' => $person->id, 'usergroup_id' => $group_id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $person_id
     * @param int $usergroup_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonGroupAction($person_id, $usergroup_id)
    {
        $person = $this->_getPersonOr404($person_id);

        foreach ($person->usergroups as $group) {
            if ($group->id == $usergroup_id) {
                return $this->createApiResponse(['exists' => true]);
            }
        }

        return $this->createApiResponse(['exists' => false]);
    }

    /**
     * @param int $person_id
     * @param int $usergroup_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deletePersonGroupAction($person_id, $usergroup_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');

        foreach ($person->usergroups as $key => $group) {
            if ($group->id == $usergroup_id) {
                if ($group->is_agent_group) {
                    return $this->createApiErrorResponse('invalid_group', 'Group is an agent group');
                }
                unset($person->usergroups[$key]);
                $this->em->persist($person);
                $this->em->flush();
                break;
            }
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonLabelsAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id);

        return $this->createApiResponse(['labels' => $this->getApiData($person->labels)]);
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postPersonLabelsAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');
        $label  = $this->in->getString('label');

        if ($label === '') {
            return $this->createApiErrorResponse('required_field', "Field 'label' missing or empty");
        }

        $person->getLabelManager()->addLabel($label);
        $this->em->persist($person);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['label' => $label],
            $this->generateUrl(
                'api_people_person_label',
                ['person_id' => $person->id, 'label' => $label],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int    $person_id
     * @param string $label
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonLabelAction($person_id, $label)
    {
        $person = $this->_getPersonOr404($person_id);

        if ($person->getLabelManager()->hasLabel($label)) {
            return $this->createApiResponse(['exists' => true]);
        } else {
            return $this->createApiResponse(['exists' => false]);
        }
    }

    /**
     * @param int    $person_id
     * @param string $label
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deletePersonLabelAction($person_id, $label)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');

        $person->getLabelManager()->removeLabel($label);
        $this->em->persist($person);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @return Response
     */
    public function getFieldsAction()
    {
        $field_manager = $this->container->getSystemService('person_fields_manager');
        $fields        = $field_manager->getFields();

        return $this->createApiResponse(['fields' => $this->getApiData($fields)]);
    }

    /**
     * @return Response
     */
    public function getGroupsAction()
    {
        $groups = $this->em->createQuery('
            SELECT g
            FROM DeskPRO:Usergroup g INDEX BY g.id
            WHERE g.is_agent_group = false AND g.sys_name IS NULL
            ORDER BY g.id
        ')->execute();

        return $this->createApiResponse(['groups' => $this->getApiData($groups)]);
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getLoginTokenAction($person_id)
    {
        if (!$this->person->hasPerm('agent_people.login_as')) {
            throw $this->createAccessDeniedException();
        }

        $person = $this->_getPersonOr404($person_id);
        $secret = sha1($person->secret_string.$person->salt);
        $token  = Util::generateStaticSecurityToken($secret, 300);

        return $this->createApiResponse([
            'person_id'        => $person_id,
            'login_token'      => $token,
            'direct_login_url' => $this->generateUrl('user_login', ['tok' => $person_id.'-'.$token], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    /**
     * @param Person $person
     *
     * @return bool
     */
    public function isPersonEditable(Person $person)
    {
        if ($this->person->can_admin) {
            return true;
        }

        if ($person->is_agent && $person->getId() != $this->person->getId()) {
            return false;
        }

        return true;
    }

    /**
     * @param int    $id
     * @param string $check_perm
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected function _getPersonOr404($id, $check_perm = '')
    {
        $person = $this->em->getRepository('DeskPRO:Person')->findOneById($id);

        if (!$person) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no person with ID $id");
        }

        if ($check_perm) {
            switch ($check_perm) {
                case 'edit':
                case 'delete':
                case 'reset_password':
                case 'manage_emails':
                case 'notes':
                    if (!$this->isPersonEditable($person)) {
                        throw $this->createAccessDeniedException('Only admins may edit other agents.');
                    }
                    if (!$this->person->hasPerm('agent_people.'.$check_perm)) {
                        throw $this->createAccessDeniedException('Insufficient permission. Required: agent_people.'.$check_perm);
                    }
                    break;

                default:
                    throw new \Exception("Uknown perm type $check_perm");
            }
        }

        return $person;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function quickSearchAction(Request $request)
    {
        /** @var \Application\DeskPRO\EntityRepository\Person $rep */
        $rep = $this->em->getRepository('DeskPRO:Person');
        $res = $rep->quickSearch(
            $request->get('query'), !$request->get('start_with'), $request->get('with_agents'),
            $request->get('exclude_org'), $request->get('limit')
        );

        $ret = [];
        foreach ($res as $item) {
            $ret[] = $item;
        }

        return $this->createApiResponse($ret);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function quickSearchEmailAction(Request $request)
    {
        /** @var \Application\DeskPRO\EntityRepository\PersonEmail $rep */
        $rep = $this->em->getRepository('DeskPRO:PersonEmail');

        return $this->createApiResponse($rep->search($request->get('query'), $request->get('limit')));
    }

    /**
     * @return Response
     */
    public function authLoginAction()
    {
        $username = $this->in->getString('email') ?: $this->in->getString('username');
        $password = $this->in->getString('password');
        $manager  = $this->container->getSystemService('authentication_manager');

        $result = $manager->authenticateFormLogin($username, $password);

        // if we are using local auth in the user interface, and we fail, try agent form login sources as well
        if (!$result->isValid()) {
            $manager = $manager->cloneForInterface('agent');
            $result  = $manager->authenticateFormLogin($username, $password);
        }

        if (!$result->isValid()) {
            return $this->createJsonResponse([
                'error_code'    => 'invalid_credentials',
                'error_message' => 'Invalid email address or password',
            ], 401);
        }

        $identity = $result->getIdentity();

        return $this->createJsonResponse([
            'success' => 'true',
            'person'  => $identity['person']->toApiData(),
        ], 200);
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonPhoneNumbersAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id);

        return $this->createApiResponse($this->getApiData($person->phone_numbers));
    }

    /**
     * @param int $person_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postPersonPhoneNumbersAction($person_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');

        try {
            if (!$number = PhoneNumber::parseNumber($this->in->getString('phone_number'))) {
                throw new \Exception('Invalid phone_number');
            }
        } catch (\Exception $e) {
            return $this->createApiErrorResponse('parse_error', $e->getMessage());
        }

        $number->label  = $this->in->getString('label');
        $number->person = $person;
        $this->em->persist($number);
        $this->em->flush($number);

        return $this->getPersonPhoneNumberAction($person_id, $number->id);
    }

    /**
     * @param int $person_id
     * @param int $number_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function getPersonPhoneNumberAction($person_id, $number_id)
    {
        $person = $this->_getPersonOr404($person_id);
        /** @var $number PhoneNumber */
        if (!$number = $this->em->find('DeskPRO:PhoneNumber', $number_id)) {
            throw new NotFoundHttpException();
        }

        if (!$number->person === $person) {
            throw new NotFoundHttpException();
        }

        return $this->createApiResponse($this->getApiData($number));
    }

    /**
     * @param int $person_id
     * @param int $number_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function postPersonPhoneNumberAction($person_id, $number_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');
        /** @var $number PhoneNumber */
        if (!$number = $this->em->find('DeskPRO:PhoneNumber', $number_id)) {
            throw new NotFoundHttpException();
        }

        if (!$number->person === $person) {
            throw new NotFoundHttpException();
        }

        if ($label = $this->in->getString('label')) {
            $number->label = $label;
        }

        $this->em->flush($number);

        return $this->getPersonPhoneNumberAction($person_id, $number_id);
    }

    /**
     * @param int $person_id
     * @param int $number_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function deletePersonPhoneNumberAction($person_id, $number_id)
    {
        $person = $this->_getPersonOr404($person_id, 'edit');
        /** @var $number PhoneNumber */
        if (!$number = $this->em->find('DeskPRO:PhoneNumber', $number_id)) {
            throw new NotFoundHttpException();
        }

        if (!$number->person === $person) {
            throw new NotFoundHttpException();
        }

        $person->phone_numbers->removeElement($number);
        $this->em->remove($number);
        $this->em->flush();

        return $this->createSuccessResponse();
    }
}
