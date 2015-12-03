import React, { PropTypes } from 'react';

export class FindAnotherAgent extends React.Component {

  static propTypes = {
    agentName: PropTypes.string,
    onClick: PropTypes.func
  };

  onClick = event => {
    event.preventDefault();
    this.props.onClick();
  };

  render() {
    return (
      <div>
        <h1>{this.props.agentName} seems to have been disconnected</h1>
        <h2>If they don't return soon, we'll find another agent for you.</h2>
        <p>
          <a href="#" onClick={this.onClick}>
            Find another agent now
          </a>
        </p>
      </div>
    );
  }
}
