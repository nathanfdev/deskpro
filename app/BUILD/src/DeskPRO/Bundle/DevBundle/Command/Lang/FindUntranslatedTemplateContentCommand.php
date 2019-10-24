<?php

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Token;

/**
 * Class FindUntranslatedTemplateContentCommand
 *
 * @package DeskPRO\Bundle\DevBundle\Command\Lang
 */
class FindUntranslatedTemplateContentCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dpdev:lang:find-untranslated-template-content')
            ->setDescription('Try to find any untranslated template content')
            ->addArgument(
                'root_templates_dir',
                InputArgument::REQUIRED,
                'Root directory for the templates for this command to search in',
                null
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templatesDir = getcwd().DIRECTORY_SEPARATOR.$input->getArgument('root_templates_dir');

        if (!($templatesDir && is_dir($templatesDir))) {
            $output->writeln("<error>Templates root directory '{$templatesDir}' is not valid</error>");

            return;
        }

        $finder = (new Finder())
            ->in($templatesDir)
            ->files()
            ->name('*.twig')
        ;

        /** @var Environment $twig */
        $twig = $this->getContainer()->get('twig');

        $output->writeln("--------");
        $output->writeln("Searching ({$finder->count()}) files...");
        $output->writeln("--------");

        $contentCount = 0;

        /** @var SplFileInfo $file */
        foreach ($finder as $filepath => $file) {
            $stream = $twig->tokenize(file_get_contents($filepath));

            $content = '';

            try {
                while ($token = $stream->next()) {
                    if ($token->getType() === Token::TEXT_TYPE) {
                        $content .= $token->getValue();
                    }
                }
            } catch (SyntaxError $e) {
            }

            if (empty($content)) {
                continue;
            }

            $parsed = $this->parseContent($content);

            if (count($parsed)) {
                $output->writeln("Found content in <info>{$file->getRelativePathname()}</info>");
                foreach ($parsed as $content) {
                    $output->writeln(" + \"{$content}\"");
                    $contentCount ++;
                }
                $output->writeln("--------");
            }
        }

        $output->writeln("Done.");
        $output->writeln("Found <info>{$contentCount}</info> fragments of content.");
    }

    /**
     * @param string $content
     * @return array
     */
    private function parseContent($content)
    {
        $stripped = preg_replace('#<\s*?style\b[^>]*>(.*?)</style\b[^>]*>#s', '', $content);
        $stripped = preg_replace('#<\s*?script\b[^>]*>(.*?)</script\b[^>]*>#s', '', $stripped);
        $stripped = preg_replace('#&(.*);#s', '', $stripped);
        $stripped = preg_replace('#</?[^>]+>#', '', $stripped);

        $parsed = str_replace('  ', '', $stripped);
        $parsed = array_filter(array_map('trim', explode(PHP_EOL, $parsed)));
        $parsed = array_filter($parsed, function ($value) {
            return preg_match('/[a-z]+/i', $value);
        });

        return $parsed;
    }
}
