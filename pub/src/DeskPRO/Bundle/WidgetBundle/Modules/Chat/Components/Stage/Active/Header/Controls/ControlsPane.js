import React, { PropTypes } from 'react';

export class ControlsPane extends React.Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div className="dpdesignportal-chat-header-controls">
        <ul>
          {this.props.children}
        </ul>
      </div>
    );
  }
}
