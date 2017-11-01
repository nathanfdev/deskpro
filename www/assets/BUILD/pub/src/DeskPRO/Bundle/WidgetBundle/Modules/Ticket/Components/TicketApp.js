import PropTypes from 'prop-types';
import React from 'react';

export class TicketApp extends React.Component {

  static propTypes = {
    children: PropTypes.node
  };

  render() {
    return this.props.children;
  }
}
