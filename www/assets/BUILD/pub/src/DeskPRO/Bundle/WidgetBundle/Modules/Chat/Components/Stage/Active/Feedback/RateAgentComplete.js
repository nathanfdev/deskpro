import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class RateAgentComplete extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-agent-rating dpdesignportal-agent-rating-complete">
        <i className="fa fa-check-circle-o"></i>
        <h1>{portalPhrases.get('portal.chat.feedback_title')}</h1>
        <p className="grey">{portalPhrases.get('portal.chat.feedback_desc')}</p>
      </div>
    );
  }
}
