import React from 'react';
import { connect } from 'react-redux';
import { onlineAgentsSelector } from '../../../../Application/Selectors/agent';

@connect(state => ({
  onlineAgents: onlineAgentsSelector(state)
}))
export class OnlineAgentsContainer extends React.Component {

  render() {
    const props = this.props;
    const child = props.children;
    const childProps = child.props;

    return React.cloneElement(child, {
      ...props,
      ...childProps
    });
  }
}
