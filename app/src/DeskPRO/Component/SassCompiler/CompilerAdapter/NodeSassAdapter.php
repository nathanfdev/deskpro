<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\SassCompiler\CompilerAdapter;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class NodeSassAdapter implements CompilerAdapterInterface
{
    /**
     * @var string
     */
    private $bin_path = '/usr/bin/node-sass';

    /**
     * @var array
     */
    private $options;

    /**
     * @var Process
     */
    private $last_proc;

    /**
     * @param string $bin_path
     * @param array $options
     */
    public function __construct($bin_path, array $options = array())
    {
        $this->bin_path = $bin_path;
        $this->options = self::getOptionsResolver()->resolve($options);
    }

    /**
     * @return OptionsResolver
     */
    private static function getOptionsResolver()
    {
        static $resolver;

        if (!$resolver) {
            $resolver = new OptionsResolver();
            $resolver->setDefaults(array(
                'output-style' => 'nested',
                'indent-type' => 'space',
                'indent-width' => 2,
                'linefeed' => 'lf',
                'source-comments' => false
            ));
            $resolver->setAllowedValues('output-style', array('nested', 'expanded', 'compact', 'compressed'));
            $resolver->setAllowedValues('indent-type', array('tab', 'space'));
            $resolver->setAllowedTypes('indent-width', 'int');
            $resolver->setAllowedValues('linefeed', array('cr', 'crlf', 'lf', 'lfcr'));
            $resolver->setAllowedTypes('source-comments', 'bool');
        }

        return $resolver;
    }

    /**
     * @param string $source_file
     * @param array $inc_paths
     * @return string
     */
    public function compile($source_file, array $inc_paths)
    {
        $args = array();

        foreach ($this->options as $name => $v) {
            if (is_bool($v)) {
                if ($v) {
                    $args[] = '--' . $name;
                }
            } else {
                $args[] = '--' . $name . '=' . escapeshellarg($v);
            }
        }

        $out_file = @tempnam(sys_get_temp_dir(), "dpcss_");
        if (!$out_file) {
            $out_file = @tempnam(dirname($source_file), "dpcss_");
        }
        if (!$out_file) {
            throw new \RuntimeException("Failed to create temporary output file in sys temp dir and source file dir");
        }

        $args[] = $source_file;
        $cmd = escapeshellcmd($this->bin_path) . ' ' . implode(' ', $args) . ' ' . escapeshellarg($out_file);
        $cwd = dirname($source_file);

        $this->last_proc = new Process($cmd, $cwd);
        $this->last_proc->setTimeout(60);
        $this->last_proc->run();

        if (!$this->last_proc->isSuccessful()) {
            throw new \InvalidArgumentException("node-sass returned error -- " . $this->last_proc->getErrorOutput());
        }

        return file_get_contents($out_file);
    }

    /**
     * @return Process
     */
    public function getLastProcess()
    {
        return $this->last_proc;
    }
}