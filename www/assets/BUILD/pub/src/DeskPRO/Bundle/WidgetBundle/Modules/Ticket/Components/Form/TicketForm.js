import React from 'react';
import { TicketFormContentContainer } from './TicketFormContentContainer';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class TicketForm extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-content dpdesignportal-open-new-ticket">
        <div className="header">
          <span className="img" />
          <h1>{portalPhrases.get('portal.tickets.new-title')}</h1>
          <p>{portalPhrases.get('portal.tickets.new-intro')}</p>
        </div>

        <div className="dpdesignportal-form">
          <TicketFormContentContainer />
        </div>
      </div>
    );
  }
}
