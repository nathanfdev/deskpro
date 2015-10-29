<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\View\File;

class FileViewHelper
{
    protected static $data = [
        'zip'     => ['file-archive-o'],
        'gz'      => ['file-archive-o'],
        'tar'     => ['file-archive-o'],
        'bz2'     => ['file-archive-o'],
        's7z'     => ['file-archive-o'],
        '7z'      => ['file-archive-o'],
        'ace'     => ['file-archive-o'],
        'pdf'     => ['file-pdf-o'],
        'gif'     => ['file-image-o'],
        'bmp'     => ['file-image-o'],
        'jpg'     => ['file-image-o'],
        'jpeg'    => ['file-image-o'],
        'png'     => ['file-image-o'],
        'txt'     => ['file-text-o'],
        'xls'     => ['file-excel-o'],
        'xlsx'    => ['file-excel-o'],
        'xlt'     => ['file-excel-o'],
        'xltx'    => ['file-excel-o'],
        'xltm'    => ['file-excel-o'],
        'xlm'     => ['file-excel-o'],
        'xlsm'    => ['file-excel-o'],
        'numbers' => ['file-excel-o'],
        'pptx'    => ['file-powerpoint-o'],
        'ppt'     => ['file-powerpoint-o'],
        'keynote' => ['file-powerpoint-o'],
        'doc'     => ['file-word-o'],
        'docx'    => ['file-word-o'],
        'dot'     => ['file-word-o'],
        'dotx'    => ['file-word-o'],
        'pages'   => ['file-word-o'],
        'mp4'     => ['file-video-o'],
        'avi'     => ['file-video-o'],
        'wmv'     => ['file-video-o'],
        'mpg'     => ['file-video-o'],
        'mp3'     => ['file-audio-o'],
        'wav'     => ['file-audio-o'],
        'wma'     => ['file-audio-o'],
    ];

    public static function getCssClassForFileExtension($file_extension)
    {
        if (array_key_exists($file_extension, self::$data)) {
            return self::$data[$file_extension];
        }

        return [''];
    }
}
