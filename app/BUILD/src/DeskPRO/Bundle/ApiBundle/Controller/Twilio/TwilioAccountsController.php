<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Twilio;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TwilioAccount;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Twilio\TwilioAccountType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Data\Countries;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TwilioAccountsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/twilio_accounts")
 * @ApiDoc(target="all", section="Voice Channel", output="DeskPRO\Bundle\AppBundle\Entity\TwilioAccount")
 */
class TwilioAccountsController extends CrudController
{
    public static $entity    = TwilioAccount::class;
    public static $type      = TwilioAccountType::class;
    public static $listOrder = 'asc';

    /**
     * @param Request $request
     *
     * @Rest\Post("/test_credentials")
     *
     * @return View
     */
    public function testCredentialsAction(Request $request)
    {
        $account    = new TwilioAccount();
        $accountSid = $request->request->get('account_sid');

        if ($accountSid) {
            // pre-load existing account entity to avoid duplicate validation errors
            $existingAccount = $this->getManager()->getRepository(TwilioAccount::class)->findOneBy([
                'accountSid' => $accountSid,
            ]);

            if ($existingAccount) {
                $account = $existingAccount;
            }
        }

        $form = $this->createForm(TwilioAccountType::class, $account);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        return new View($this->wrap([
            'successful' => true,
        ]));
    }

    /**
     * @Rest\Get("/{account}/available_numbers")
     *
     * @param TwilioAccount $account
     * @param Request       $request
     *
     * @return View
     */
    public function getAvailableNumbersAction(TwilioAccount $account, Request $request)
    {
        $adapter     = $this->get('twilio_adapter');
        $query       = $request->query;
        $countryCode = strtoupper($query->get('country_code'));
        $options     = [];

        $region = $query->get('region');
        if ($region) {
            if (strlen($region) > 2) {
                if ($countryCode === 'US' && $stateCode = Countries::getUsStateCode($region)) {
                    // check as country code
                    $options['InRegion'] = $stateCode;
                } else {
                    // check as rate center
                    $options['InRateCenter'] = $region;
                }
            } else {
                // check as country code
                $options['InRegion'] = $region;
            }
        }
        if ($query->get('phrase')) {
            $options['Contains'] = $query->get('phrase');
        }

        $types = $query->get('types');
        if (!$types || !is_array($types)) {
            $types = [];
        }

        $numbers = [];
        foreach ($types as $type) {
            $numbers = array_merge($numbers, $adapter->getAvailablePhoneNumbers($account, $countryCode, $type, $options));
        }

        return new View($this->wrap($numbers));
    }

    /**
     * @Rest\Get("/{account}/existing_numbers")
     *
     * @param TwilioAccount $account
     * @param Request       $request
     *
     * @return View
     */
    public function getExistingNumbersAction(TwilioAccount $account, Request $request)
    {
        $page   = $request->query->getInt('page', 1);
        $result = $this->get('twilio_adapter')->getExistingPhoneNumbers($account, $page);

        return new View($this->wrap($result->getRecords(), [
            'pag_num'  => $result->getPageNum(),
            'has_next' => $result->hasNext(),
        ]));
    }
}
