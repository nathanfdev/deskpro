import React from 'react';
import { connect } from 'react-redux';
import { TypingEvent } from './TypingEvent';
import { agentNameSelector, agentLastTypingTimeSelector } from '../../../../../Selectors/chat';

@connect(state => ({
  agentName: agentNameSelector(state),
  agentLastTypingTime: agentLastTypingTimeSelector(state)
}))
export class TypingEventContainer extends React.Component {

  render() {
    return <TypingEvent {...this.props} />;
  }
}
