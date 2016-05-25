import React, { PropTypes } from 'react';

export class WaitingLoader extends React.Component {

  static propTypes = {
    agentName: PropTypes.string
  };

  render() {
    return (
      <div>
        <h1>{this.props.agentName} seems to have been disconnected</h1>
        <h2>We're looking for a new agent.</h2>
        <div className="search-dots">
          <div className="dot-1"></div>
          <div className="dot-2"></div>
          <div className="dot-3"></div>
          <div className="dot-4"></div>
          <div className="dot-5"></div>
        </div>
      </div>
    );
  }
}
