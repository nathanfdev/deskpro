<?php

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
