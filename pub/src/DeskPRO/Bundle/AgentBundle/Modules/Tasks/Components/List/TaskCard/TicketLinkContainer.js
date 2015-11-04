import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { TicketLink } from './TicketLink';

@connect()
export class TicketLinkContainer extends React.Component {

  static propTypes = {
    ticket: PropTypes.string
  };

  render() {
    return (
      <TicketLink {...this.props} />
    );
  }
}
