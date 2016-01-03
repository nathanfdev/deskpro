import React from 'react';
import PortalPhrases from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class TicketFormSubmitted extends React.Component {

  render() {
    return (
      <div className="ticket-success-blurb">
        <div className="icon-mark"></div>
        <h1>{PortalPhrases.get('portal.tickets.thank_you')}</h1>
        <p>{PortalPhrases.get('portal.tickets.thank_you_desc')}</p>
      </div>
    );
  }
}
