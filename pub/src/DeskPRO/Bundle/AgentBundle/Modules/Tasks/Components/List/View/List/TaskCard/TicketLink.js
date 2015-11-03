import React, { PropTypes } from 'react';

export class TicketLink extends React.Component {

  static propTypes = {
    ticket: PropTypes.string
  };

  render() {
    return (
      <span>
        <span className="dpw--card-disc"/>
        <span className="dpwd--card-line-item">
          <i className="fa fa-link"/> <a href="#">{this.props.ticket}</a>
        </span>
      </span>
    );
  }
}
