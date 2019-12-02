<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1574181135 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $db     = $this->getDbConnection();
        $topics = $db->fetchAll('SELECT id, content FROM `topics`');
        foreach ($topics as $topic) {
            $content = $topic['content'];
            if (preg_match_all('/<img[^>]+>/', $content, $matches)) {
                $imgs = $matches[0];
                foreach ($imgs as $img) {
                    preg_match('/img\(([A-Z0-9]+)\//', $img, $matches);
                    if ($matches) {
                        $blobAuth = $matches[1];

                        $blob = $this->getDbConnection()->fetchAssoc('SELECT dim_w, dim_h FROM `blobs` WHERE authcode = ?', [$blobAuth]);
                        if (!$blob) {
                            continue;
                        }
                        preg_match_all('/(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?/', $img, $matches);
                        $attributes = [];
                        for ($i = 0; $i < count($matches[0]); ++$i) {
                            $attributes[$matches[1][$i]] = $matches[2][$i];
                        }
                        $attributes['data-width']  = $blob['dim_w'];
                        $attributes['width']       = $blob['dim_w'];
                        $attributes['data-height'] = $blob['dim_h'];
                        if (isset($attributes['src'])) {
                            $attributes['data-src'] = $attributes['src'];
                        }
                        $attributes['src'] = $this->generateSvg($blob['dim_w'], $blob['dim_h']);
                        $newImg            = '<img '.implode(' ', array_map(function ($k, $v) {
                            return "$k=\"$v\"";
                        }, array_keys($attributes), array_values($attributes))).' />';
                        $content = str_replace($img, $newImg, $content);
                    }
                }
                $db->executeUpdate('UPDATE `topics` SET content = :content WHERE id = :topic_id', ['content' => $content, 'topic_id' => $topic['id']]);
            }
        }
    }

    private function generateSvg($width, $height)
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 '.$width.' '.$height.'" ><rect x="0" y="0" style="fill:white;stroke:grey;stroke-width:3px;" id="e1_rectangle" width="'.$width.'" height="'.$height.'"/></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
