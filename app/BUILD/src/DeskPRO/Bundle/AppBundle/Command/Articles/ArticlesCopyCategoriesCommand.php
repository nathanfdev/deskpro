<?php

namespace DeskPRO\Bundle\AppBundle\Command\Articles;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleAttachment;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\CustomDataArticle;
use Application\DeskPRO\EntityRepository\Blob as BlobRepository;
use DeskPRO\Component\Util\MatchConfig;
use DeskPRO\Component\Util\StringUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('with-custom-data', 'c', InputOption::VALUE_NONE, 'Also copy custom data')
            ->addOption('skip-existing', 'p', InputOption::VALUE_NONE, 'If slug exists just skip the article')
            ->setHelp(<<<EOT
Use this command to copy articles from one category to another.

This will create <info>new</info> articles with new attachments and inline images.

Use --with-custom-data or -c to copy custom data as well
Use --skip-existing or -s to skip existing articles (matched by slug-#BRAND_ID), otherwise a new article will be
added with incremented #BRAND_ID

Usage example is:

   $>/path/to/deskpro/bin/console dp:articles:copy-categories -m 1:11 2:12 3:13 -c
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
        $container                   = $this->getContainer();

        $container->get('audit_log.doctrine_listener')->disableListener();

        $articleCategoriesRepository = $this->em->getRepository(ArticleCategory::class);
        /** @var \Application\DeskPRO\EntityRepository\Article $articlesRepository */
        $articlesRepository = $this->em->getRepository(Article::class);

        $brandSettingsResolver       = $container->get('brand_aware_settings_resolver');
        $objectRouter                = $container->get('object_router');
        $slugManager                 = $container->get('content_slug_manager');
        $mapping                     = $input->getArgument('mapping');

        foreach ($mapping as $categoriesMap) {
            list($oldCategoryId, $newCategoryId) = array_map('intval', explode(':', $categoriesMap, 2));
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

                $newSlug = sprintf('%s-%d', $oldArticle->getSlug(), $toBrand->getId());
                if ($input->getOption('skip-existing') && $articlesRepository->findOneBy(['slug' => $newSlug])) {
                    $output->writeln("<fg=yellow>Skipping existing article with slug $newSlug</>");

                    continue;
                }

                $content = $oldArticle->getContentHtml();

                $blobAuthcodes        = StringUtils::gatherInlineAttachments($content, $matchConfig);
                $inlineBlobsInMessage = $blobRepository->getByAuthCodes($blobAuthcodes) ?: [];

                foreach ($inlineBlobsInMessage as $oldInlineBlob) {
                    $newInlineBlob = $blobStorage->createBlobRecordFromString(
                        $blobStorage->copyBlobRecordToString($oldInlineBlob),
                        sprintf('%s-%d', $oldInlineBlob->getFilename(), $toBrand->getId()),
                        $oldInlineBlob->getContentType()
                    );
                    $this->em->persist($newInlineBlob);
                    $content = preg_replace(
                        "#($fromBrandUrl|(http://.+?))/file.php/{$oldInlineBlob->getAuthcode()}/{$oldInlineBlob->getFilename()}#mi",
                        "{$toBrandUrl}/file.php/{$newInlineBlob->getAuthcode()}/{$newInlineBlob->getFilename()}",
                        $content
                    );
                }

                $newArticle
                    ->addToCategory($newCategory)
                    ->setPerson($oldArticle->getPerson())
                    ->setReviewInterval($oldArticle->getReviewInterval())
                    ->setDateNextReview($oldArticle->getDateNextReview())
                    ->setDateEnd($oldArticle->getDateEnd())
                    ->setEndAction($oldArticle->getEndAction())
                    ->setLanguage($oldArticle->getLanguage())
                    ->setTitle($oldArticle->getTitle())
                    ->setStatusCode($oldArticle->getStatusCode())
                    ->setContent($content)
                    ->setContentInput($oldArticle->getContentInput())
                    ->setContentInputType($oldArticle->getContentInputType())
                    ->setIcon($oldArticle->getIcon()) // won't create new icon - must be re-uploaded if needed
                ;
                $slugManager->ensureValidSlug($newArticle);

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

                if ($input->getOption('with-custom-data')) {
                    foreach ($oldArticle->getCustomData() as $oldCustomDatum) {
                        $newCustomDatum = new CustomDataArticle();
                        $newCustomDatum
                            ->setArticle($newArticle)
                            ->setField($oldCustomDatum->getField())
                            ->setRootField($oldCustomDatum->getRootField())
                            ->setInput($oldCustomDatum->getInput())
                            ->setValue($oldCustomDatum->getValue())
                        ;
                        $newArticle->addCustomData($newCustomDatum);
                    }
                }

                $this->em->persist($newArticle);
                $this->em->flush();

                $newArticleUrl = $this->getContainer()->get('brand_stack')->pushTemporary(
                    $toBrand,
                    function () use ($objectRouter, $newArticle) {
                        return $objectRouter->getPortalUrl($newArticle);
                    }
                );

                $oldArticleUrl = $this->getContainer()->get('brand_stack')->pushTemporary(
                    $fromBrand,
                    function () use ($objectRouter, $oldArticle) {
                        return $objectRouter->getPortalUrl($oldArticle);
                    }
                );

                $output->writeln([
                    '<comment>[NEW ARTICLE]</comment>',
                    sprintf('#%d %s', $oldArticle->getId(), $oldArticleUrl),
                    sprintf('=> #%d %s', $newArticle->getId(), $newArticleUrl),
                ]);
            }
        }
    }
}
