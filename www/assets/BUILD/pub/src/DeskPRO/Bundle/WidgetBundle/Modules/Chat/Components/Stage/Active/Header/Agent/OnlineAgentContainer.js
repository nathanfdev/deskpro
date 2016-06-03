import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { agentAcceptTimeoutSelector } from '../../../../../../Application/Selectors/dpWindow';
import {
  chatIdSelector,
  agentNameSelector,
  agentAvatarSelector,
  departmentNameSelector,
  hasAssignedMessageSelector
} from '../../../../../Selectors/chat';
import { OnlineAgent } from './OnlineAgent';
import { WaitingAgent } from './WaitingAgent';

@connect(state => ({
  chatId:             chatIdSelector(state),
  acceptTimeout:      agentAcceptTimeoutSelector(state),
  agentName:          agentNameSelector(state),
  agentAvatar:        agentAvatarSelector(state),
  departmentName:     departmentNameSelector(state),
  hasAssignedMessage: hasAssignedMessageSelector(state)
}))
export class OnlineAgentContainer extends React.Component {

  static propTypes = {
    hasAssignedMessage: PropTypes.bool
  };

  render() {
    return this.props.hasAssignedMessage ? <OnlineAgent {...this.props} /> : <WaitingAgent {...this.props} />;
  }
}
