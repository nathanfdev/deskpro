import React from 'react';
import history from '../../../Services/history';

export class TicketForm extends React.Component {

  render() {
    return (
      <div>
        Ticket form
        <button onClick={() => history.replace('ticket/form_submitted')} />
      </div>
    );
  }
}
