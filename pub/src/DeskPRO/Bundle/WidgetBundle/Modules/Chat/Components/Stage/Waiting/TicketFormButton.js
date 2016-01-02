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
        It’s taking longer than expected to find an agent to take your chat.
        Would you like to <a href="#" onClick={this.onOpenTicketForm}>submit</a> a ticket instead?
      </div>
    );
  }
}
