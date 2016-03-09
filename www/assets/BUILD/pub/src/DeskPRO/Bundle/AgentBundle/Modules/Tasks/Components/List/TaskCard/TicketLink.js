import React, { PropTypes } from 'react';

export class TicketLink extends React.Component {

  static propTypes = {
    ticket: PropTypes.string
  };

  render() {
    return (
      <div style={{display: 'inline-block', width: '30%'}}>
        <div className="dpwd--card-line-item"
             style={{display: 'inline-block', position: 'relative', paddingLeft: 20, overflow: 'hidden', width: '100%'}}>
          <i className="fa fa-link" style={{position: 'absolute', left: 2, top: 2}} />
          <span title={this.props.ticket}>{this.props.ticket}</span>
        </div>
      </div>
    );
  }
}
