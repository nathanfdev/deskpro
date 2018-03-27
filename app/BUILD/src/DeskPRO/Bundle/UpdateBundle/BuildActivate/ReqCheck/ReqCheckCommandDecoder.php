<?php

namespace DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck;

class ReqCheckCommandDecoder
{
    /**
     * @param string $commandOutput
     *
     * @throws ReqCheckException
     *
     * @return array
     */
    public function decodeResults($commandOutput)
    {
        if (!preg_match('#\-{10,}BEGIN\-{10,}(.*?)\-{10,}END\-{10,}#s', $commandOutput, $match)) {
            throw ReqCheckException::createCommandException('Requirements checker returned unexpected output');
        }

        $results = @json_decode(trim($match[1]), true);

        if (!is_array($results)) {
            throw ReqCheckException::createCommandException('Requirements checker returned invalid output that could not be decoded');
        }

        if (!isset($results['failed_requirements']) || !isset($results['failed_recommendations'])) {
            throw ReqCheckException::createCommandException('Requirements checker returned unexpected results');
        }

        return $results;
    }
}
