<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\BlobAuthTransformer;
use DeskPRO\Component\Util\IpUtils;
use Doctrine\ORM\EntityManager;
use Guzzle\Http\Mimetypes;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BlobAuthType.
 */
class BlobAuthType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param DeskproBlobStorage $blobStorage
     */
    public function __construct(EntityManager $em, DeskproBlobStorage $blobStorage)
    {
        $this->em          = $em;
        $this->blobStorage = $blobStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new BlobAuthTransformer($this->em));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], 2000);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'error_bubbling' => false,
            'data_class'     => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return HiddenType::class;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (is_array($data)) {
            if (isset($data['blob_auth'])) {
                $event->setData($data['blob_auth']);
            } elseif (isset($data['url'])) {
                try {
                    $client  = new Client();
                    $request = new Request('GET', $data['url']);

                    if (!IpUtils::isUrlUserCallable($data['url'])) {
                        throw new \InvalidArgumentException("URL is not user callable");
                    }

                    $content     = $client->send($request)->getBody()->getContents();
                    $filename    = preg_replace('#(^(.*)/(.*?)(\?.*)?$)#', '${3}', $data['url']);
                    $contentType = Mimetypes::getInstance()->fromFilename($filename) ?: 'application/octet-stream';

                    $blob = $this->blobStorage->createBlobRecordFromString($content, $filename, $contentType, ['is_temp' => true]);
                } catch (\Exception $e) {
                    $blob = null;
                }

                $event->setData($blob ? $blob->getAuthcode() : null);
            } else {
                $event->setData(null);
            }
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getForm()->getData();
        if ($data instanceof Blob) {
            $data->setIsTemp(false);
        }
    }
}
