import React from 'react';
import history from '../../../../../Services/history';

export class TicketFormButton extends React.Component {

  onOpenTicketForm = event => {
    event.preventDefault();
    history.replace('/ticket/form');
  };

  render() {
    return (
      <div>
        <p>It’s taking longer than expected to find an agent to take your chat.</p>
        <p>Would you like to <a href="#" onClick={this.onOpenTicketForm}>submit</a> a ticket instead?</p>
      </div>
    );
  }
}
