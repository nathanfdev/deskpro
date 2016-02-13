import React, { PropTypes } from 'react';

export class TicketApp extends React.Component {

  static propTypes = {
    children: PropTypes.node
  };

  render() {
    return this.props.children;
  }
}
