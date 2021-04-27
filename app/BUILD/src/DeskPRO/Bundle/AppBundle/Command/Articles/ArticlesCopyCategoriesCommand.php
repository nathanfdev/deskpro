<?php

namespace DeskPRO\Bundle\AppBundle\Command\Articles;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleAttachment;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\EntityRepository\Blob as BlobRepository;
use DeskPRO\Component\Util\MatchConfig;
use DeskPRO\Component\Util\StringUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ArticlesCopyCategoriesCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:articles:copy-categories')
            ->setDescription('Copy articles from specified categories to other categories.')

            ->addArgument('mapping', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Categories mapping')
            ->setHelp(<<<EOT
Use this command to copy articles from one category to another.

This will create <info>new</info> articles with new attachments and inline images.

Usage example is:

   $>/path/to/deskpro/bin/console dp:articles:copy-categories -m 1:11 2:12 3:13
EOT
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('audit_log.doctrine_listener')->disableListener();
        $articleCategoriesRepository = $this->em->getRepository(ArticleCategory::class);
        $container                   = $this->getContainer();
        $brandSettingsResolver       = $container->get('brand_aware_settings_resolver');
        $objectRouter                = $container->get('object_router');
        $mapping                     = $input->getArgument('mapping');
        foreach ($mapping as $categoriesMap) {
            list($oldCategoryId, $newCategoryId) = array_map('intval', explode(':', $categoriesMap));
            /** @var ArticleCategory|null $newCategory */
            if (!$newCategory = $articleCategoriesRepository->find($newCategoryId)) {
                $output->writeln(sprintf('<error>[ERROR] Wrong new article category id: %d</error>', $newCategoryId));

                continue;
            }
            /** @var ArticleCategory|null $oldCategory */
            if (!$oldCategory = $articleCategoriesRepository->find($oldCategoryId)) {
                $output->writeln(sprintf('<error>[ERROR] Wrong old article category id: %d</error>', $oldCategoryId));

                continue;
            }

            $fromBrand = $oldCategory->getBrand();
            $toBrand   = $newCategory->getBrand();
            /** @var Article[] $articles */
            $articles = $oldCategory->getArticles();

            /** @var DeskproBlobStorage $blobStorage */
            $blobStorage = $container->get('blob.storage');

            $matchConfig = new MatchConfig(
                $container->get('router')->generate('serve_blob', ['blob_auth_id' => '00000', 'filename' => '11111']),
                '00000',
                '11111'
            );
            $fromBrandUrl = trim($brandSettingsResolver->getSetting('core.deskpro_url', $fromBrand), '/');
            $toBrandUrl   = trim($brandSettingsResolver->getSetting('core.deskpro_url', $toBrand), '/');
            /** @var BlobRepository $blobRepository */
            $blobRepository = $this->em->getRepository(Blob::class);

            foreach ($articles as $oldArticle) {
                $newArticle = new Article();

                $content = $oldArticle->getContentHtml();

                $blobAuthcodes        = StringUtils::gatherInlineAttachments($content, $matchConfig);
                $inlineBlobsInMessage = $blobRepository->getByAuthCodes($blobAuthcodes) ?: [];

                foreach ($inlineBlobsInMessage as $oldInlineBlob) {
                    $newInlineBlob = $blobStorage->createBlobRecordFromString(
                        $blobStorage->copyBlobRecordToString($oldInlineBlob),
                        sprintf('%s-%d', $oldInlineBlob->getFilename(), $toBrand->getId()),
                        $oldInlineBlob->getContentType()
                    );
                    $this->em->persist($oldInlineBlob);
                    $content = preg_replace(
                        "#($fromBrandUrl|(http://.+?))/file.php/{$oldInlineBlob->getAuthcode()}/{$oldInlineBlob->getFilename()}#mi",
                        "{$toBrandUrl}/file.php/{$newInlineBlob->getAuthcode()}/{$newInlineBlob->getFilename()}",
                        $content
                    );
                }

                $newArticle
                    ->addToCategory($newCategory)
                    ->setLanguage($oldArticle->getLanguage())
                    ->setTitle($oldArticle->getTitle())
                    ->setStatusCode($oldArticle->getStatusCode())
                    ->setContent($content)
                    ->setContentInput($oldArticle->getContentInput())
                    ->setContentInputType($oldArticle->getContentInputType())
                    ->setIcon($oldArticle->getIcon()) // won't create new icon - must be re-uploaded if needed
                    ->setSlug(sprintf('%s-%d', $oldArticle->getSlug(), $toBrand->getId()))
                ;

                foreach ($oldArticle->getLabels() as $label) {
                    $newArticle->addLabel($label);
                }

                foreach ($oldArticle->getAttachments() as $attachment) {
                    $newAttachment = new ArticleAttachment();
                    $blob          = $attachment->getBlob();
                    $newBlob       = $blobStorage->createBlobRecordFromString(
                        $blobStorage->copyBlobRecordToString($blob),
                        sprintf('%s-%d', $blob->getFilename(), $toBrand->getId()),
                        $blob->getContentType()
                    );
                    $this->em->persist($blob);
                    $newAttachment->setBlob($newBlob);
                    $newArticle->addAttachment($newAttachment);
                    $this->em->persist($newAttachment);
                }

                $this->em->persist($newArticle);
                $this->em->flush();

                $output->writeln([
                    '<comment>[NEW ARTICLE]</comment>',
                    sprintf('#%d %s', $oldArticle->getId(), $objectRouter->getPortalUrl($oldArticle)),
                    sprintf('=> #%d %s', $newArticle->getId(), $objectRouter->getPortalUrl($newArticle)),
                ]);
            }
        }
    }
}
