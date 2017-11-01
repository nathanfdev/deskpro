import PropTypes from 'prop-types';
import React from 'react';

export class Offline extends React.Component {
  static propTypes = {
    online: PropTypes.bool.isRequired
  };

  render() {
    return !this.props.online ? (
      <div className="active-chat-agent-offline">
        <i className="fa fa-exclamation-triangle"></i>

        <h1>Agent is currently offline.</h1>

        <p>Your messages will be delivered by email.</p>
      </div>)
    : null;
  }
}
