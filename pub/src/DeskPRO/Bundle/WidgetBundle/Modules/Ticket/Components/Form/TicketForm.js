import React from 'react';
import { TicketFormContentContainer } from './TicketFormContentContainer';

export class TicketForm extends React.Component {

  render() {
    return (
      <div>
        Ticket form
        <TicketFormContentContainer />
      </div>
    );
  }
}
