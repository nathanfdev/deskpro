<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Search
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Search\ContentType\Mysql;

use Application\DeskPRO\Search\ContentType\AbstractContentType;
use Application\DeskPRO\Search\Adapter\MysqlAdapter;
use Application\DeskPRO\Search\SearcherResult\ResultInterface;
use Application\DeskPRO\Search\Indexer\Document;

class ChatConversation extends AbstractContentType
{
	const ENTITY_NAME = 'DeskPRO:ChatConversation';

	public function objectToDocument($convo)
	{
		$data = array();
		$data['id'] = $convo['id'];
		$data['content_type'] = 'chat_conversation';
		$data['content'] = $convo['title'] . "\n" . $convo['content'] . "\n";

		$content = array();
		$content[] = $ticket['subject'];
		foreach ($convo->messages as $message) {
			if ($message->is_sys) continue;

			if ($message->is_html) {
				$content[] = strip_tags($message->content);
			} else {
				$content[] = $message->content;
			}
		}

		$data['content'] = implode(" ", $content);

		$doc = Document::newFromArray($data);

		return $doc;
	}
}
