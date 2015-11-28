import React, { PropTypes } from 'react';

export class MessageAvatar extends React.Component {

  static propTypes = {
    user: PropTypes.object
  };

  render() {
    return (
      <div className="dpdesignportal-message-avatar">
        <i className="fa fa-user"></i>
      </div>
    );
  }
}
