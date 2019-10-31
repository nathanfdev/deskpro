import PropTypes from 'prop-types';
import React from 'react';

export class TicketMessageMenu  extends React.Component {

  static propTypes = {
    message:            PropTypes.object,
    phoneCall:          PropTypes.object,
    canDeleteRecording: PropTypes.bool,
    canDeleteMessage:   PropTypes.bool,
    deleteRecord:       PropTypes.func,
    deleteMessage:      PropTypes.func
  };

  deleteRecordings = () => {
    this.props.deleteRecord(this.props.phoneCall.get('id'));
  };

  deleteMessage = () => {
    const { message, deleteMessage } = this.props;
    deleteMessage(message.ticket.id, message.id);
  };

  render() {
    const { canDeleteRecording, canDeleteMessage } = this.props;

    return (
      <div className="deskpro-menu-outer">
        <div className="deskpro-menu-inner ">
          <div className="deskpro-menu">
            <div className="deskpro-menu-scrollup" />
            <div className="deskpro-menu-scrolldown" />
            <ul className="ticket-message-edit-menu">
              {canDeleteRecording
                && <li onClick={this.deleteRecordings} className="delete-recordings">Delete recording</li>}
              {canDeleteMessage
                && <li onClick={this.deleteMessage} className="delete-recordings">Delete message</li>}
            </ul>
          </div>
        </div>
      </div>);
  }
}
