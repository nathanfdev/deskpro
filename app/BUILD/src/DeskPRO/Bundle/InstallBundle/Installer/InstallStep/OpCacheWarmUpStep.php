<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

class OpCacheWarmUpStep extends AbstractStep
{
    /**
     * @var string
     */
    private $authcode;

    public function run()
    {
        $this->writeBigTitle('OpCache warmup');
        $this->writeln('');

        $env = $this->getContext()->getDpEnv();
        if ($env->getDatManager()->hasTxtFile('server_info_auth')) {
            $this->authcode = $env->getDatManager()->readTxtFile('server_info_auth');
        } else {
            $this->authcode = '';
        }

        $warmupUrl = $this->getSession()->getWebUrl().'/__serverinfo/opcache/warmup?auth='.$this->authcode;

        $ctx = stream_context_create(['http' => ['timeout' => 10, 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]]);
        @file_get_contents($warmupUrl, null, $ctx);

        $this->writeln('Done!');
    }

    public function isComplete()
    {
        return false;
    }
}
