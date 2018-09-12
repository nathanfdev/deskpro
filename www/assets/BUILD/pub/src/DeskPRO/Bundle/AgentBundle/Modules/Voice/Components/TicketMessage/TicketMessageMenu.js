import PropTypes from 'prop-types';
import React from 'react';

export class TicketMessageMenu  extends React.Component {

  static propTypes = {
    message: PropTypes.object,
    record:  PropTypes.object
  };


  deleteRecordings = () => {
    console.log('delete recordings popup, message id = ', this.props.message.id, 'recording id = ', this.props.record.get('blob_id'));
  };

  render() {
    return (
      <div className="deskpro-menu-outer">
        <div className="deskpro-menu-inner ">
          <div className="deskpro-menu">
            <div className="deskpro-menu-scrollup" />
            <div className="deskpro-menu-scrolldown" />
            <ul className="ticket-message-edit-menu">
              <li onClick={this.deleteRecordings} className="delete-recordings">Delete recordings</li>
            </ul>
          </div>
        </div>
      </div>);
  }
}
