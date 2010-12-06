<?php

namespace Application\DeskPRO\Integration\XenForo\View;

class SimpleJson extends \XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		return $this->_params;
	}
}