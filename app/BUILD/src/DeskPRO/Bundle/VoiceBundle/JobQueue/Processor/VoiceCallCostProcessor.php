<?php

namespace DeskPRO\Bundle\VoiceBundle\JobQueue\Processor;

use Application\DeskPRO\JobQueue\Processor\AbstractJobProcessor;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VoiceCallCostProcessor.
 */
class VoiceCallCostProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'voice_call_cost_processor';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param Connection    $connection
     * @param EntityManager $em
     */
    public function __construct(Connection $connection, EntityManager $em)
    {
        parent::__construct($connection);
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data, array $job)
    {
        try {
            /** @var AbstractVoicePhoneCallParticipant $participant */
            $participant = $this->em->getRepository(AbstractVoicePhoneCallParticipant::class)->findOneBy([
                'callSid' => $data['call_sid'],
            ]);
            if (!$participant) {
                return;
            }

            $phoneCall = $participant->getPhoneCall();
            $phoneCall->addCost($data['cost']);
            $phoneCall->setCostCurrency($data['currency']);

            $participant->addCost($data['cost']);
            $participant->setCostCurrency($data['currency']);

            $this->em->flush();

            $this->runSuccessHandler($job);
        } catch (\Exception $e) {
            $this->runExceptionHandler($job, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['call_sid', 'cost', 'currency']);
    }
}
