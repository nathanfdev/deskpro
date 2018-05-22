import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { loadAll, collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { agentAcceptTimeoutSelector } from '../../../../../../Application/Selectors/dpWindow';
import { findAnotherAgent } from '../../../../../Actions/chatActions';
import {
  agentIdSelector,
  chatIdSelector,
  agentNameSelector,
  agentAvatarSelector,
  departmentSelector,
  hasAssignedMessageSelector
} from '../../../../../Selectors/chat';
import { OnlineAgent } from './OnlineAgent';
import { WaitingAgent } from './WaitingAgent';
import { AgentDisconnected } from '../../Disconnect/AgentDisconnected';
import { FindAnotherAgent } from '../../Disconnect/FindAnotherAgent';
import { WaitingLoader } from '../../Disconnect/WaitingLoader';

@connect(state => ({
  agentId:            agentIdSelector(state),
  chatId:             chatIdSelector(state),
  acceptTimeout:      agentAcceptTimeoutSelector(state),
  agentName:          agentNameSelector(state),
  agentAvatar:        agentAvatarSelector(state),
  departmentId:       departmentSelector(state),
  allChatDepartments: collectionSelectorFactory('ChatDepartment', 'all')(state),
  hasAssignedMessage: hasAssignedMessageSelector(state)
}))
export class OnlineAgentContainer extends React.Component {

  static propTypes = {
    dispatch:           PropTypes.func,
    agentId:            PropTypes.number,
    agentName:          PropTypes.string,
    agentAvatar:        PropTypes.object,
    hasAssignedMessage: PropTypes.bool,
    chatId:             PropTypes.number
  };

  constructor(props) {
    super(props);
    this.state = {
      disconnected: false
    };
  }

  componentDidMount() {
    this.props.dispatch(loadAll('ChatDepartment'));
  }

  componentWillReceiveProps(props) {
    const disconnected = !props.agentId && this.props.agentId;
    this.setState({ disconnected });
  }

  onStartFindAgent = () => {
    const { dispatch, chatId } = this.props;

    this.setState({ disconnected: true });
    dispatch(findAnotherAgent(chatId));
  };

  render() {
    if (!this.props.hasAssignedMessage) {
      return <WaitingAgent {...this.props} />;
    }

    if (this.props.agentId) {
      return <OnlineAgent {...this.props} />;
    }

    const { agentName, agentAvatar } = this.props;

    return (
      <AgentDisconnected agentAvatar={agentAvatar}>
        {this.state.disconnected
          ? <WaitingLoader agentName={agentName} />
          : <FindAnotherAgent onClick={this.onStartFindAgent} agentName={agentName} />
        }
      </AgentDisconnected>
    );
  }
}
export default OnlineAgentContainer;
