import React from 'react';
import { connect } from 'react-redux';
import { onlineAgentsSelector, primaryAgentSelector } from '../../../../Application/Selectors/peopleSelectors';
import { helpPopupStartButtonSelector } from '../../../../Application/Selectors/dpWindow';

@connect(state => ({
  onlineAgents:         onlineAgentsSelector(state),
  primaryAgent:         primaryAgentSelector(state),
  helpPopupStartButton: helpPopupStartButtonSelector(state)
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
