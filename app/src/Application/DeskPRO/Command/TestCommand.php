<?php

/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/


/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\Command;

use Orb\Log\Logger;
use Orb\Log\Writer\ArrayWriter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;

use Orb\Util\Arrays;
use Orb\Util\Strings;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\Route;

class TestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dp:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$find_replace = array('ban-circle' => 'ban',
			'bar-chart' => 'bar-chart-o',
			'beaker' => 'flask',
			'bell-alt' => 'bell',
			'bell' => 'bell-o',
			'bitbucket-sign' => 'bitbucket-square',
			'bookmark-empty' => 'bookmark-o',
			'building' => 'building-o',
			'calendar-empty' => 'calendar-o',
			'check-empty' => 'square-o',
			'check-minus' => 'minus-square-o',
			'check-sign' => 'check-square',
			'check' => 'check-square-o',
			'chevron-sign-down' => 'chevron-circle-down',
			'chevron-sign-left' => 'chevron-circle-left',
			'chevron-sign-right' => 'chevron-circle-right',
			'chevron-sign-up' => 'chevron-circle-up',
			'circle-arrow-down' => 'arrow-circle-down',
			'circle-arrow-left' => 'arrow-circle-left',
			'circle-arrow-right' => 'arrow-circle-right',
			'circle-arrow-up' => 'arrow-circle-up',
			'circle-blank' => 'circle-o',
			'cny' => 'rub',
			'collapse-alt' => 'collapse-o',
			'collapse-top' => 'caret-square-o-up',
			'collapse' => 'caret-square-o-down',
			'comment-alt' => 'comment-o',
			'comments-alt' => 'comments-o',
			'copy' => 'files-o',
			'cut' => 'scissors',
			'dashboard' => 'tachometer',
			'double-angle-down' => 'angle-double-down',
			'double-angle-left' => 'angle-double-left',
			'double-angle-right' => 'angle-double-right',
			'double-angle-up' => 'angle-double-up',
			'download-alt' => 'download',
			'download' => 'arrow-circle-o-down',
			'edit-sign' => 'pencil-square',
			'edit' => 'pencil-square-o',
			'ellipsis-horizontal' => 'ellipsis-h',
			'ellipsis-vertical' => 'ellipsis-v',
			'envelope-alt' => 'envelope-o',
			'exclamation-sign' => 'exclamation-circle',
			'expand-alt' => 'expand-o',
			'expand' => 'caret-square-o-right',
			'external-link-sign' => 'external-link-square',
			'eye-close' => 'eye-slash',
			'eye-open' => 'eye',
			'facebook-sign' => 'facebook-square',
			'facetime-video' => 'video-camera',
			'file-alt' => 'file-o',
			'file-text-alt' => 'file-text-o',
			'flag-alt' => 'flag-o',
			'folder-close-alt' => 'folder-o',
			'folder-close' => 'folder',
			'folder-open-alt' => 'folder-open-o',
			'food' => 'cutlery',
			'frown' => 'frown-o',
			'fullscreen' => 'arrows-alt',
			'github-sign' => 'github-square',
			'google-plus-sign' => 'google-plus-square',
			'group' => 'users',
			'h-sign' => 'h-square',
			'hand-down' => 'hand-o-down',
			'hand-left' => 'hand-o-left',
			'hand-right' => 'hand-o-right',
			'hand-up' => 'hand-o-up',
			'hdd' => 'hdd-o',
			'heart-empty' => 'heart-o',
			'hospital' => 'hospital-o',
			'indent-left' => 'outdent',
			'indent-right' => 'indent',
			'info-sign' => 'info-circle',
			'keyboard' => 'keyboard-o',
			'legal' => 'gavel',
			'lemon' => 'lemon-o',
			'lightbulb' => 'lightbulb-o',
			'linkedin-sign' => 'linkedin-square',
			'meh' => 'meh-o',
			'microphone-off' => 'microphone-slash',
			'minus-sign-alt' => 'minus-square',
			'minus-sign' => 'minus-circle',
			'mobile-phone' => 'mobile',
			'moon' => 'moon-o',
			'move' => 'arrows',
			'off' => 'power-off',
			'ok-circle' => 'check-circle-o',
			'ok-sign' => 'check-circle',
			'ok' => 'check',
			'paper-clip' => 'paperclip',
			'paste' => 'clipboard',
			'phone-sign' => 'phone-square',
			'picture' => 'picture-o',
			'pinterest-sign' => 'pinterest-square',
			'play-circle' => 'play-circle-o',
			'play-sign' => 'play-circle',
			'plus-sign-alt' => 'plus-square',
			'plus-sign' => 'plus-circle',
			'pushpin' => 'thumb-tack',
			'question-sign' => 'question-circle',
			'remove-circle' => 'times-circle-o',
			'remove-sign' => 'times-circle',
			'remove' => 'times',
			'reorder' => 'bars',
			'resize-full' => 'expand',
			'resize-horizontal' => 'arrows-h',
			'resize-small' => 'compress',
			'resize-vertical' => 'arrows-v',
			'rss-sign' => 'rss-square',
			'save' => 'floppy-o',
			'screenshot' => 'crosshairs',
			'share-alt' => 'share',
			'share-sign' => 'share-square',
			'share' => 'share-square-o',
			'sign-blank' => 'square',
			'signin' => 'sign-in',
			'signout' => 'sign-out',
			'smile' => 'smile-o',
			'sort-by-alphabet-alt' => 'sort-alpha-desc',
			'sort-by-alphabet' => 'sort-alpha-asc',
			'sort-by-attributes-alt' => 'sort-amount-desc',
			'sort-by-attributes' => 'sort-amount-asc',
			'sort-by-order-alt' => 'sort-numeric-desc',
			'sort-by-order' => 'sort-numeric-asc',
			'sort-down' => 'sort-asc',
			'sort-up' => 'sort-desc',
			'stackexchange' => 'stack-overflow',
			'star-empty' => 'star-o',
			'star-half-empty' => 'star-half-o',
			'sun' => 'sun-o',
			'thumbs-down-alt' => 'thumbs-o-down',
			'thumbs-up-alt' => 'thumbs-o-up',
			'time' => 'clock-o',
			'trash' => 'trash-o',
			'tumblr-sign' => 'tumblr-square',
			'twitter-sign' => 'twitter-square',
			'unlink' => 'chain-broken',
			'upload-alt' => 'upload',
			'upload' => 'arrow-circle-o-up',
			'warning-sign' => 'exclamation-triangle',
			'xing-sign' => 'xing-square',
			'youtube-sign' => 'youtube-square',
			'zoom-in' => 'search-plus',
			'zoom-out' => 'search-minus',
		);
		
		$path = DP_ROOT . '/src/Application/AdminInterfaceBundle';
		$files = Finder::create()->in($path)->files("*.twig");

		
		echo "\n";
	}
}
