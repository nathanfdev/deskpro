import PropTypes from 'prop-types';
import React from 'react';

export class MessageBody extends React.Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div className="dpdesignportal-message-content">
        {this.props.children}
      </div>
    );
  }
}
