<?php

/**
 * DeskPRO.
 */

namespace DpTest;

use Application\DeskPRO\Entity\Template;
use Application\EmailBundle\Entity\SendmailSource;
use Application\EmailBundle\Templating\Templates\TemplateCustom;
use DpSys\Kernel\ApiKernel;
use DpSys\Kernel\MessengerKernel;
use DpSys\Kernel\PortalKernel;

abstract class AbstractKernelAwareTestCase extends DeskProTestCase
{
    /**
     * @var ApiKernel|null
     */
    protected static $api_kernel;

    /**
     * @var PortalKernel|null
     */
    protected static $portal_kernel;

    /**
     * @var MessengerKernel|null
     */
    protected static $messengerKernel;

    /**
     * @var bool
     */
    protected static $rebootKernel = false;

    protected static $lastInstalledDataSet;

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    abstract protected function getContainer();

    /**
     * @param mixed $service
     *
     * @return object
     */
    protected function get($service)
    {
        return $this->getContainer()->get($service);
    }

    /**
     * @param array $server - lets you override $_SERVER variables
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    public function getClient($server = [])
    {
        // TODO: we need a good way of setting the 'HTTP_HOST' key on $server to the dev's machine
        // maybe just use the a global we declare in config.test.php ?? might be best option.
        $client = $this->getContainer()->get('test.client');
        $client->setServerParameters($server);

        return $client;
    }

    /**
     * @param bool $force_reboot
     *
     * @return ApiKernel
     */
    protected function getApiKernel($force_reboot = false)
    {
        if (self::$rebootKernel) {
            $force_reboot       = true;
            self::$rebootKernel = false;
        }

        if (self::$api_kernel && !$force_reboot) {
            return self::$api_kernel;
        }

        if (self::$api_kernel) {
            self::$api_kernel->shutdown();
            $kernel = self::$api_kernel;
        } else {
            require_once DP_ROOT.'/sys/Kernel/ApiKernel.php';
            $kernel = new ApiKernel($GLOBALS['DP_ENV']->getEnvId(), $GLOBALS['DP_ENV']->isDebug(), $GLOBALS['DP_ENV']);
        }

        $kernel->boot();

        self::$api_kernel = $kernel;

        return self::$api_kernel;
    }

    /**
     * @param bool $force_reboot
     *
     * @return PortalKernel
     */
    protected function getPortalKernel($force_reboot = false)
    {
        if (self::$rebootKernel) {
            $force_reboot       = true;
            self::$rebootKernel = false;
        }

        if (self::$messengerKernel && !$force_reboot) {
            return self::$messengerKernel;
        }

        if (self::$messengerKernel) {
            self::$messengerKernel->shutdown();
            $kernel = self::$messengerKernel;
        } else {
            require_once DP_ROOT.'/sys/Kernel/PortalKernel.php';
            $kernel = new PortalKernel($GLOBALS['DP_ENV']->getEnvId(), $GLOBALS['DP_ENV']->isDebug(), $GLOBALS['DP_ENV']);
        }

        $kernel->boot();

        self::$messengerKernel = $kernel;

        return self::$messengerKernel;
    }

    /**
     * @param bool $force_reboot
     *
     * @return MessengerKernel
     */
    protected function getMessengerKernel($force_reboot = false)
    {
        if (self::$rebootKernel) {
            $force_reboot       = true;
            self::$rebootKernel = false;
        }

        if (self::$messengerKernel && !$force_reboot) {
            return self::$messengerKernel;
        }

        if (self::$messengerKernel) {
            self::$messengerKernel->shutdown();
            $kernel = self::$messengerKernel;
        } else {
            require_once DP_ROOT.'/sys/Kernel/MessengerKernel.php';
            $kernel = new MessengerKernel($GLOBALS['DP_ENV']->getEnvId(), $GLOBALS['DP_ENV']->isDebug(), $GLOBALS['DP_ENV']);
        }

        $kernel->boot();

        self::$messengerKernel = $kernel;

        return self::$messengerKernel;
    }

    /**
     * Install a data set. To ensure a reinstall, send a flag.
     *
     * By default, if the data set you want is already installed before,
     * then nothing happens.
     *
     * @param string $dataSetId
     * @param bool   $reinstall
     * @param bool   $recreateStructure
     */
    public function installDataSet($dataSetId, $reinstall = false, $recreateStructure = false)
    {
        if (self::$lastInstalledDataSet === $dataSetId) {
            // the same data set is already loaded
            if (!$reinstall) {
                // no indication to reinstall, exit
                return;
            }
        }

        $this->get('dataset_manager')->install($dataSetId, $recreateStructure);

        // remember that we installed this
        self::$lastInstalledDataSet = $dataSetId;

        self::$rebootKernel = true;
    }

    /**
     * @param $entity
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepo($entity)
    {
        return $this->getEntityManager()->getRepository($entity);
    }

    /**
     * @param $entity
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository($entity)
    {
        return $this->getRepo($entity);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->get('doctrine.orm.default_entity_manager');
    }

    /**
     * Assert an email with the correct subject was sent to the address provided.
     *
     * Optionally, a check on what the email message contains can be done.
     *
     * Be sure to be somewhat specific with the email message "contains" text because
     * it asserts on the entire raw email message.
     *
     * @param $to
     * @param $subject
     * @param null $message_contains
     */
    public function assertEmailWithSubjectWasSentTo($to, $subject, $message_contains = null)
    {
        $email_info = $this->getLastEmailInfo($to);

        $this->assertNotFalse($email_info, 'an email was sent');
        $this->assertEquals($subject, $email_info['subject'], 'email subject is correct');
        $this->assertContains($to, $email_info['to'], 'email sent to the correct address');
        if ($message_contains) {
            $processed_msg = preg_replace('#[\s]+#', ' ', $email_info['message']);
            $this->assertContains($message_contains, $processed_msg, 'email contains the right text');
        }
    }

    /**
     * Takes an email address and returns useful info to assert on for the last email
     * that entered the queue for this address. This should cover most uses cases
     * for email testing.
     *
     * If you need more than that, it will take some more work, but some methods
     * that might help are: getSendmailSources() and getMessageFromEmailSource().
     *
     * The array returned is:
     *
     *  message: the text of the message being sent (the entire raw email)
     *  to: the TO header of the email
     *  from: the FROM header of the email
     *  subject: the SUBJECT header of the email
     *
     * @param $email_address_string
     *
     * @return array|false false if no emails in the queue, the last one in queue otherwise
     */
    protected function getLastEmailInfo($email_address_string)
    {
        $sources = $this->getSendmailSources($email_address_string);
        /** @var SendmailSource $last_source */
        $last_source = end($sources);

        if (!$last_source) {
            return false;
        }

        return [
            'from'    => $last_source->getHeaderFrom(),
            'to'      => $last_source->getHeaderTo(),
            'subject' => $last_source->getHeaderSubject(),
            'message' => $this->getMessageFromEmailSource($last_source),
        ];
    }

    /**
     * @param $email
     *
     * @return SendmailSource[]
     */
    protected function getSendmailSources($email)
    {
        /** @var \Application\EmailBundle\EntityRepository\SendmailSourceRepository $repo */
        $repo = $this->getRepo('EmailBundle:SendmailSource');

        $query = $repo->createQueryBuilder('s')
            ->select('s')
            ->where("s.to_emails LIKE '%$email%'")
            ->orderBy('s.date_created', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param SendmailSource $last_source
     *
     * @return string
     */
    public function getMessageFromEmailSource($last_source)
    {
        $message = $this->get('deskpro.blob_storage')->copyBlobRowIdToString($last_source->getBlob()->getId());

        return $message;
    }

    /**
     * Simulates saving a custom email template.
     *
     * @param $name
     * @param $template_code
     */
    public function saveCustomEmailTemplate($name, $template_code)
    {
        $tt                = new Template();
        $tt->name          = $name;
        $tt->template_code = $template_code;
        $template          = new TemplateCustom($name, $tt);
        $this->get('templating.email.template_set')->saveTemplate($template);
    }

    /**
     * @return \Symfony\Component\Validator\ValidatorInterface
     */
    public function getValidator()
    {
        return $this->getContainer()->get('validator');
    }
}
