<?php

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * @deprecated Use the FieldFanager with the field manager service
 */
class ArticleFields extends AbstractFields
{
	const ENTITY_CLASS = 'Application\\DeskPRO\\Entity\\CustomDefArticle';
	const ENTITY_NAME  = 'DeskPRO:CustomDefArticle';
}
