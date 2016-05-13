import React from 'react';
import { TicketFormContentContainer } from './TicketFormContentContainer';

export class TicketForm extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-content dpdesignportal-open-new-ticket">
        <TicketFormContentContainer />
      </div>
    );
  }
}
