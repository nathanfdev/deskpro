<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Doctrine\DBAL\Connection;
use Orb\Util\Strings;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FixBlobPathsCommand extends ContainerAwareCommand
{
    /**
     * @var bool
     */
    private $isReal = false;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:fix-blob-paths');
        $this->setHelp('Fixes hard-coded blob paths in content (such as articles or news)');
        $this->addOption('run', null, InputOption::VALUE_NONE, 'Run the changes. If not specified, only a dry-run will be run.');
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->isReal = $input->getOption('run');

        $db = $this->getContainer()->getDb();

        $tables = [
            'articles'  => ['articles', 'content'],
            'news'      => ['news', 'content'],
            'downloads' => ['downloads', 'content'],
            'feedback'  => ['feedback', 'content'],
        ];

        $output->write('Fetching content that needs updating...');

        $queries = [];
        foreach ($tables as $t) {
            $queries[] = "(SELECT {$t[0]}.id, '{$t[0]}' AS tablename FROM {$t[0]} WHERE {$t[0]}.{$t[1]} LIKE '%file.php/%')";
        }

        $rows = $db->fetchAll(implode(' UNION ', $queries));

        $output->write(sprintf("%d rows need updating\n", count($rows)));

        foreach ($rows as $r) {
            $content_field = $tables[$r['tablename']][1];
            $this->handleRow($r['tablename'], $content_field, $r['id']);
        }

        return 0;
    }

    private function handleRow($table, $content_field, $row_id)
    {
        $db = $this->getContainer()->getDb();

        $content = $db->fetchColumn("
            SELECT $content_field AS content
            FROM $table
            WHERE id = $row_id
        ");

        $orig_content = $content;

        if (!$content_field) {
            return;
        }

        // trailing 0 = was a db row. todo: add handling of fs -> db as well
        preg_match_all('#/file.php/(\d+)([A-Z]+0)/#', $content, $matches, PREG_SET_ORDER);

        if (!$matches) {
            return;
        }

        $blob_ids = [];
        foreach ($matches as $m) {
            $blob_ids[] = $m[1];
        }

        $blob_ids = array_unique($blob_ids);

        $blobs = $db->fetchAllKeyed('SELECT * FROM blobs WHERE id IN (?)', [$blob_ids], 'id', [Connection::PARAM_INT_ARRAY]);

        if (!$blobs) {
            return;
        }

        foreach ($matches as $m) {
            $blob_id = $m[1];
            $url_str = $m[0];
            $blob    = @$blobs[$blob_id];

            if (!$blob) {
                continue;
            }

            $filename_safe = Strings::utf8_accents_to_ascii($blob['filename']);
            $filename_safe = preg_replace('#[^a-zA-Z0-9\-_\.]#', '-', $filename_safe);
            $filename_safe = preg_replace('#\-{2,}#', '-', $filename_safe);

            $auth_id = $blob['authcode'];

            $url = $this->getContainer()->get('router')->generate('serve_blob', ['blob_auth_id' => $auth_id, 'filename' => $filename_safe], UrlGeneratorInterface::ABSOLUTE_PATH);
            $url = Strings::extractRegexMatch('#/file.php/\d+[A-Z0-9]+/#', $url, 0);

            if ($url === $url_str) {
                continue;
            }

            if (!$this->isReal) {
                echo "[$table.$row_id] $url_str -> $url\n";
            } else {
                $content = str_replace($url_str, $url, $content);
            }
        }

        if ($this->isReal && $orig_content !== $content) {
            $db->update($table, [$content_field => $content], ['id' => $row_id]);
            echo "[$table.$row_id] Updated\n";
        }
    }
}
