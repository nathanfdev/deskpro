import React from 'react';
import Isvg from 'react-inlinesvg';
import IMSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/topbar/IM.svg';

function IMButton() {
  return (
    <span className="ui image avatar im" id="im-button">
      <Isvg src={IMSvg} />
    </span>
  );
}

export default IMButton;
