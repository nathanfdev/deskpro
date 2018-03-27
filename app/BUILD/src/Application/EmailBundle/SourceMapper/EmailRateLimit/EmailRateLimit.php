<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SourceMapper\EmailRateLimit;

use Application\DeskPRO\Entity\DataStore;
use Application\EmailBundle\Entity\SendmailSource;
use Orb\Util\Arrays;
use Orb\Util\Dates;
use Symfony\Component\DependencyInjection\Container;

class EmailRateLimit implements EmailRateLimitInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var \Application\EmailBundle\EntityRepository\SendmailSourceRepository
     */
    private $sourceRepository;

    /**
     * @var array
     */
    private $limits;

    /**
     * The min time frame to apply limits to. This allows the rate limit
     * time to be 'reset'.
     *
     * @var \DateTime|null
     */
    private $reset_date;

    /**
     * Array of email account IDs that we are applying limits to.
     *
     * @var array|null
     */
    private $in_account_ids;

    /**
     * EmailRateLimit constructor.
     *
     * @param Container      $container
     * @param array          $limits
     * @param array|null     $in_account_ids
     * @param \DateTime|null $reset_date
     */
    public function __construct(Container $container, array $limits, array $in_account_ids = null, \DateTime $reset_date = null)
    {
        $this->container        = $container;
        $this->sourceRepository = $container->get('doctrine.orm.default_entity_manager')->getRepository(SendmailSource::class);
        $this->limits           = $limits;
        $this->reset_date       = $reset_date;
        $this->in_account_ids   = $in_account_ids;
    }

    /**
     * @param array $message The message array (as returned by the SourceMapper)
     *
     * @return bool
     */
    public function isLimited(array $message)
    {
        // Not actually sending
        if ($message['status'] === 'error' || $message['status'] === 'aborted') {
            return false;
        }

        // No limits
        if (!$this->limits) {
            return false;
        }

        // Not applied to this message because not using an account we care about
        if ($this->in_account_ids && !in_array($message['email_account_id'], $this->in_account_ids)) {
            return false;
        }

        foreach ($this->limits as $options) {
            $seconds  = $options['time'];
            $limit    = $options['count'];
            $actions  = $options['actions'];
            $breached = false;

            $date = new \DateTime('@'.(time() - $seconds));
            if ($this->reset_date && $this->reset_date > $date) {
                $date = $this->reset_date;
            }

            // case where this message itself has enough
            // recipients to go over the limit
            if ($message['num_targets'] > $limit) {
                $breached = true;
            } else {
                $count = $this->sourceRepository->countSendingBetween($date, null, $this->in_account_ids);
                if (($count + $message['num_targets']) > $limit) {
                    $breached = true;
                }
            }

            if ($breached) {
                $this->runActions($options);

                if (in_array('rate_limit', $actions)) {
                    return true;
                }

                return false;
            }

            if ($date === $this->reset_date) {
                break;
            }
        }

        return false;
    }

    private function runActions(array $options)
    {
        $id      = Arrays::generateHash($options);
        $seconds = $options['time'];
        $limit   = $options['count'];
        $actions = $options['actions'];

        $title = sprintf('Sendmail Rule(%d messages within %s)', $limit, Dates::secsToReadable($seconds));

        $em = $this->container->get('doctrine.orm.default_entity_manager');

        foreach ($actions as $act) {
            switch ($act) {
                case 'log_account_warning':
                    if (!defined('DPC_SITE_FLAG_SILENCE_SENDMAIL_RATE_WARNING')) {
                        \DpShutdown::add(function () use ($em, $id, $seconds, $limit, $actions, $options, $title) {
                            /** @var DataStore $logWarningRec */
                            $logWarningRec = $em->getRepository(DataStore::class)->getByName('sendmail_rate_limit.'.$id, true);
                            $lastTime = $logWarningRec->getData('last_log', null);

                            if (!$lastTime || $lastTime < (time() - $options['time'])) {
                                $tmpdata = new \Application\DeskPRO\Entity\TmpData();
                                $tmpdata->setType('log_account_warning');
                                $tmpdata->setData('message', $title);
                                $tmpdata->date_expire = new \DateTime('+30 minutes');
                                $em->persist($tmpdata);
                                $em->flush();

                                $url = DP_MA_SERVER_SECURE.'/cloud/call/'.DPC_SITE_ID.'/'.$tmpdata->getCode();

                                try {
                                    $client = new \Zend\Http\Client(null, ['timeout' => 15, 'sslverifypeer' => false]);
                                    $client->setMethod(\Zend\Http\Request::METHOD_GET);
                                    $client->setUri($url);
                                    $r = $client->send();
                                } catch (\Exception $e) {
                                    error_log('Failed to log_account_warning: '.$e->getMessage());
                                }
                            }

                            $logWarningRec->setData('last_log', time());
                            $em->persist($logWarningRec);
                            $em->flush();
                        });
                    }
                    break;

                case 'log_suspicious':
                    \DpShutdown::add(function () use ($em, $id, $seconds, $limit, $actions, $options, $title) {
                        /** @var DataStore $logWarningRec */
                        $logWarningRec = $em->getRepository(DataStore::class)->getByName('sendmail_rate_limit.'.$id, true);
                        $lastTime = $logWarningRec->getData('last_log', null);

                        if (!$lastTime || $lastTime < (time() - $options['time'])) {
                            $tmpdata = new \Application\DeskPRO\Entity\TmpData();
                            $tmpdata->setType('log_suspicious');
                            $tmpdata->setData('message', $title);
                            $tmpdata->date_expire = new \DateTime('+30 minutes');
                            $em->persist($tmpdata);
                            $em->flush();

                            $url = DP_MA_SERVER_SECURE.'/cloud/call/'.DPC_SITE_ID.'/'.$tmpdata->getCode();

                            try {
                                $client = new \Zend\Http\Client(null, ['timeout' => 15, 'sslverifypeer' => false]);
                                $client->setMethod(\Zend\Http\Request::METHOD_GET);
                                $client->setUri($url);
                                $r = $client->send();
                            } catch (\Exception $e) {
                                error_log('Failed to log_account_warning: '.$e->getMessage());
                            }
                        }

                        $logWarningRec->setData('last_log', time());
                        $em->persist($logWarningRec);
                        $em->flush();
                    });
                    break;

                case 'cancel_site':
                    \DpShutdown::add(function () use ($em, $id, $seconds, $limit, $actions, $options, $title) {
                        $tmpdata = new \Application\DeskPRO\Entity\TmpData();
                        $tmpdata->setType('cancel_for_abuse');
                        $tmpdata->setData('message', $title);
                        $tmpdata->date_expire = new \DateTime('+30 minutes');
                        $em->persist($tmpdata);
                        $em->flush();

                        $url = DP_MA_SERVER_SECURE.'/cloud/call/'.DPC_SITE_ID.'/'.$tmpdata->getCode();

                        try {
                            $client = new \Zend\Http\Client(null, ['timeout' => 15, 'sslverifypeer' => false]);
                            $client->setMethod(\Zend\Http\Request::METHOD_GET);
                            $client->setUri($url);
                            $r = $client->send();
                        } catch (\Exception $e) {
                            error_log('Failed to cancel site: '.$e->getMessage());
                        }
                    });

                    break;
            }
        }
    }
}
