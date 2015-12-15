import React from 'react';
import { connect } from 'react-redux';
import { TypingEvent } from './TypingEvent';
import { agentNameSelector } from '../../../../../Selectors/chat';

@connect(state => ({
  agentName: agentNameSelector(state)
}))
export class TypingEventContainer extends React.Component {

  render() {
    return <TypingEvent {...this.props} />;
  }
}
