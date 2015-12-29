import React from 'react';
import { TicketFormContentContainer } from './TicketFormContentContainer';

export class TicketForm extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-content dpdesignportal-open-new-ticket">
        <div className="header">
          <span className="img" />
          <h1>Open a new ticket</h1>
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut.</p>
        </div>

        <div className="dpdesignportal-form">
          <TicketFormContentContainer />
        </div>
      </div>
    );
  }
}
