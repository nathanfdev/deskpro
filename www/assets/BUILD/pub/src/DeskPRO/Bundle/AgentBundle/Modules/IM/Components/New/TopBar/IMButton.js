import React from 'react';
import Isvg from 'react-inlinesvg';

function IMButton() {
  return (
    <span className="ui image avatar im" id="im-button">
      <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/IM.svg`} />
    </span>
  );
}

export default IMButton;
