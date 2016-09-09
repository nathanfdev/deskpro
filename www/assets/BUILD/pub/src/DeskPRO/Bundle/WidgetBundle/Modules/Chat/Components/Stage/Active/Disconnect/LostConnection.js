import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export default class LostConnection extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-agent-rating">
        <h1>
          <span>{portalPhrases.get('portal.chat.lost_connection')}</span>
        </h1>
        <div className="dpdesignportal-agent-rating-buttons" />
      </div>
    );
  }
}
