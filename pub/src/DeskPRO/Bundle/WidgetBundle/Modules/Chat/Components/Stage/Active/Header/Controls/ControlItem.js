import React, { PropTypes } from 'react';

export class ControlItem extends React.Component {

  static propTypes = {
    children: PropTypes.any,
    onClick: PropTypes.func.isRequired
  };

  onClick = event => {
    event.preventDefault();
    this.props.onClick(event);
  };

  render() {
    return (
      <li>
        <a href="#" className="dpdesignportal-chat-header-control-item" onClick={this.onClick}>
          {this.props.children}
        </a>
      </li>
    );
  }
}
