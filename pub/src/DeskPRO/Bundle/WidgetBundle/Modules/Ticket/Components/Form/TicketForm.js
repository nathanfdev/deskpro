import React from 'react';
import history from '../../../../Services/history';
import { TicketFormLoaderContainer } from './TicketFormLoaderContainer';

export class TicketForm extends React.Component {

  render() {
    return (
      <div>
        Ticket form
        <TicketFormLoaderContainer />
        <button onClick={() => history.replace('ticket/form_submitted')} />
      </div>
    );
  }
}
