import React from 'react';
import { connect } from 'react-redux';
import { AgentDisconnected } from './AgentDisconnected';

@connect()
export class AgentDisconnectedContainer extends React.Component {

  render() {
    return <AgentDisconnected {...this.props} />;
  }
}
