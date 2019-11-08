<?php

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class PhraseDiffCommand
 *
 * @package DeskPRO\Bundle\DevBundle\Command\Lang
 */
class PhraseDiffCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dpdev:lang:diff-phrases')
            ->setDescription('Find a diff between two translation files')
            ->addArgument(
                'file_a',
                InputArgument::REQUIRED,
                'File A',
                null
            )
            ->addArgument(
                'file_b',
                InputArgument::REQUIRED,
                'File B',
                null
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filepathA = getcwd().DIRECTORY_SEPARATOR.$input->getArgument('file_a');
        $filepathB = getcwd().DIRECTORY_SEPARATOR.$input->getArgument('file_b');

        if (!is_readable($filepathA)) {
            throw new \RuntimeException("File A [{$filepathA}] is not readable");
        }

        if (!is_readable($filepathB)) {
            throw new \RuntimeException("File B [{$filepathB}] is not readable");
        }

        $phrasesA = Yaml::parse(file_get_contents($filepathA));
        $phrasesB = Yaml::parse(file_get_contents($filepathB));

        $added = array_diff_key($phrasesB, $phrasesA);
        $removed = array_diff_key($phrasesA, $phrasesB);

        $output->writeln('');
        $output->writeln('- ADDED (By ID) -------------------');
        if (count($added)) {
            foreach ($added as $id => $phrase) {
                $output->writeln(sprintf('<fg=green;options=bold> + </><options=bold>%s:</> %s', $id, print_r($phrase, true)));
            }
        } else {
            $output->writeln('<fg=cyan> None.</>');
        }
        $output->writeln('-----------------------------------');

        $output->writeln('');
        $output->writeln('- REMOVED (By ID) -----------------');
        if (count($removed)) {
            foreach ($removed as $id => $phrase) {
                $output->writeln(sprintf('<fg=red;options=bold> - </><options=bold>%s:</> %s', $id, print_r($phrase, true)));
            }
        } else {
            $output->writeln('<fg=cyan> None.</>');
        }
        $output->writeln('-----------------------------------');
        $output->writeln('');

        $hashing = function ($value) {
            return [$value, md5(serialize($value))];
        };

        $phrasesAHashed = array_map($hashing, $phrasesA);
        $phrasesBHashed = array_map($hashing, $phrasesB);

        $phraseDiff = [];

        // Super-inefficient comparison
        foreach ($phrasesAHashed as $idA => $hashedA) {
            foreach ($phrasesBHashed as $idB => $hashedB) {
                if ($idA === $idB) {
                    if ($hashedA[1] !== $hashedB[1]) {
                        $phraseDiff[$idA] = [$hashedA[0], $hashedB[0]];
                    }
                }
            }
        }

        $output->writeln('- CHANGES (By Phrase) -------------');
        if (count($phraseDiff)) {
            foreach ($phraseDiff as $id => $diff) {
                $output->writeln(sprintf(
                    '<fg=cyan;options=bold> > </><options=bold>%s:</> <fg=red>%s</> →  <fg=green>%s</>',
                    $id,
                    print_r($diff[0], true),
                    print_r($diff[1], true)
                ));
            }
        } else {
            $output->writeln('<fg=cyan> None.</>');
        }
        $output->writeln('-----------------------------------');
        $output->writeln('');

        $output->writeln(' - Added (By ID): '.count($added));
        $output->writeln(' - Removed (By ID): '.count($removed));
        $output->writeln(' - Changes (By Phrase): '.count($phraseDiff));
        $output->writeln('');
    }
}
