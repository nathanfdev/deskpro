import React, { PropTypes } from 'react';

export class ControlItem extends React.Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <li>
        <a href="#" className="dpdesignportal-chat-header-control-item">
          {this.props.children}
        </a>
      </li>
    );
  }
}
