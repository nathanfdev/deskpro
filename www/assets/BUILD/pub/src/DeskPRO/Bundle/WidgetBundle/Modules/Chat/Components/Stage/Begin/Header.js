import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class Header extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-collect-user-info-header">
        <span className="img" />
        <span className="text">{portalPhrases.get('portal.chat.starting')}</span>
      </div>
    );
  }
}
