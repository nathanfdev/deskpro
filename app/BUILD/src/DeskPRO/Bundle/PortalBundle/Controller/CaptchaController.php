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

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\TmpData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CaptchaController.
 */
class CaptchaController extends \Gregwar\CaptchaBundle\Controller\CaptchaController
{
    /**
     * @param string $key
     *
     * @Route("/generate-captcha/{key}", name="gregwar_captcha.generate_captcha")
     *
     * @return Response
     */
    public function generateCaptchaAction($key)
    {
        $options      = $this->container->getParameter('gregwar_captcha.config');
        $session      = $this->get('session');
        $whitelistKey = $options['whitelist_key'];
        $keys         = $session->get($whitelistKey, []);
        if (!$keys || ($keys && !in_array($key, $keys))) {
            $session->set($whitelistKey, array_merge($keys, [$key]));
            $generator = $this->container->get('gregwar_captcha.generator');
            $generator->getCaptchaCode($options);
            $persistKeys = [
                'phrase',
                'width',
                'height',
                'distortion',
                'length',
                'quality',
                'background_color',
                'text_color',
            ];
            $persistOptions = [];
            foreach ($persistKeys as $persistKey) {
                $persistOptions[$persistKey] = $options[$persistKey];
            }
            $session->set($key, $persistOptions);
            $session->save();
        }

        return parent::generateCaptchaAction($key);
    }

    /**
     * @Route("/generate-api-captcha/{token}", name="gregwar_captcha.generate_api_captcha")
     *
     * @param string $token
     *
     * @return Response
     */
    public function generateApiCaptchaAction($token)
    {
        $options   = $this->container->getParameter('gregwar_captcha.config');
        $generator = $this->container->get('gregwar_captcha.generator');
        $generator->getCaptchaCode($options);

        $persistKeys = [
            'phrase',
            'width',
            'height',
            'distortion',
            'length',
            'quality',
            'background_color',
            'text_color',
        ];

        // delete old captcha codes
        $em = $this->get('doctrine.orm.default_entity_manager');
        $em
            ->createQueryBuilder()
            ->delete(TmpData::class, 't')
            ->where('t.name = :name')
            ->setParameter('name', 'api_captcha.'.$token)
            ->getQuery()
            ->execute()
        ;

        // create a new captcha code
        $tmpData = new TmpData();
        $tmpData->setName('api_captcha.'.$token);
        $tmpData->setDateExpire(new \DateTime('+1 hour'));
        foreach ($persistKeys as $persistKey) {
            $tmpData->setData($persistKey, $options[$persistKey]);
        }

        $em->persist($tmpData);
        $em->flush();

        $response = new Response($generator->generate($options));
        $response->headers->set('Content-type', 'image/jpeg');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }
}
