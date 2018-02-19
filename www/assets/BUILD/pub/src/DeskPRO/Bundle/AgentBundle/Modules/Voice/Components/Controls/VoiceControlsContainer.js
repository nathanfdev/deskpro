import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { voiceParticipantsSelector } from '../../Selectors/agents';
import VoiceControls from './VoiceControls';
import { hangup, toggleMute, toggleHold, addAgent, transferCall, cancelInvite } from '../../Actions/clientActions';
import { connectionsSelector } from '../../Selectors/client';
import { onlineAgentsSelector } from '../../../Agent/Selectors/agents';

@connect(state => ({
  me:             meSelector(state),
  agents:         voiceParticipantsSelector(state),
  connections:    connectionsSelector(state),
  onlineAgentIds: onlineAgentsSelector(state)
}))
class VoiceControlsContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    me:             PropTypes.object,
    agents:         PropTypes.object,
    onlineAgentIds: PropTypes.object,
    connections:    PropTypes.object,
    ticketId:       PropTypes.number,
    tabRef:         PropTypes.func,
    onEndCall:      PropTypes.func
  };

  static defaultProps = {
    onEndCall: () => {}
  };

  constructor(props) {
    super(props);
    this.state = {
      mute:               false,
      hold:               false,
      status:             null,
      participants:       [],
      addTarget:          null,
      addTargetType:      null,
      transferTarget:     null,
      transferTargetType: null
    };
  }

  componentWillMount() {
    const connection = this.getConnection();

    if (connection) {
      this.setState({
        status: 'connecting'
      });
    }
  }

  componentDidMount() {
    const { tabRef, onEndCall } = this.props;

    tabRef({
      isCallActive: this.isCallActive,
      endCall:      this.onEndCall
    });

    const connection = this.getConnection();
    if (!connection) {
      return;
    }

    this.interval = setInterval(() => {
      const connectionStatus = connection.status();
      const { me } = this.props;
      const { status, participants } = this.state;

      if (connectionStatus === 'open') {
        if (participants.contains(me.get('id')) || connection.message.Outbound) {
          this.setState({
            status: 'active'
          });
        } else {
          this.setState({
            status: 'connected'
          });
        }
      } else if (connectionStatus === 'closed' && status !== 'closed') {
        onEndCall();
        this.setState({
          status: 'closed'
        });
      }
    }, 1000);

    if (window.DeskPRO_Window) {
      const messageBroker = window.DeskPRO_Window.getMessageBroker();

      messageBroker.addMessageListener('agent.voice.conference.hold', this.onExternalSetHold);
      messageBroker.addMessageListener('agent.voice.conference.status', this.onConferenceStatus);
    }
  }

  componentWillUnmount() {
    this.props.tabRef(null);
    clearInterval(this.interval);

    if (window.DeskPRO_Window) {
      const messageBroker = window.DeskPRO_Window.getMessageBroker();

      messageBroker.removeMessageListener('agent.voice.conference.hold', this.onExternalSetHold);
      messageBroker.removeMessageListener('agent.voice.conference.status', this.onConferenceStatus);
    }
  }

  onExternalSetHold = (event) => {
    const connection = this.getConnection();
    if (!connection) {
      return;
    }

    if (connection.message.CallId !== parseInt(event.call_id, 10)) {
      return;
    }

    this.setState({
      hold: !!event.hold
    });
  };

  onConferenceStatus = (event) => {
    const { me } = this.props;
    const eventName = event.StatusCallbackEvent;
    const newState  = {};

    // change current call status
    if (event.agent_id === me.get('id') && eventName === 'participant-leave') {
      newState.status = 'closed';
    }

    // update participant list
    if (event.agent_participants) {
      const { addTarget, transferTarget } = this.state;

      newState.participants = event.agent_participants;
      if (addTarget && newState.participants.contains(addTarget.get('id'))) {
        newState.addTarget     = null;
        newState.addTargetType = null;
      }
      if (transferTarget && newState.participants.contains(transferTarget.get('id'))) {
        newState.transferTarget     = null;
        newState.transferTargetType = null;
      }
    }

    // update hold status on join conference
    if (event.hold) {
      newState.hold = event.hold;
    }

    this.setState(newState);
  };

  getConnection() {
    const { connections, ticketId } = this.props;
    return connections.filter(connection => parseInt(connection.message.TicketId, 10) === parseInt(ticketId, 10)).first();
  }

  addAgent = (target, type) => {
    const { dispatch } = this.props;
    const connection = this.getConnection();
    if (!connection) {
      return;
    }

    dispatch(addAgent(connection, target, type));
    this.setState({
      addTarget:     target,
      addTargetType: type
    });
  };

  cancelInvite = (target, type) => {
    const { dispatch } = this.props;
    const connection = this.getConnection();

    dispatch(cancelInvite(connection.message.CallId, target, type));
  };

  endCall = () => {
    const { dispatch } = this.props;
    const connection = this.getConnection();
    if (!connection) {
      return;
    }

    dispatch(hangup(connection));
  };

  sendDigits = (digit) => {
    const connection = this.getConnection();
    connection.sendDigits(`${digit}`);
  };

  toggleMute = () => {
    const { dispatch } = this.props;
    const mute = !this.state.mute;
    const connection = this.getConnection();
    if (!connection) {
      return;
    }

    this.setState({ mute });
    dispatch(toggleMute(connection, mute));
  };

  toggleHold = () => {
    const { dispatch } = this.props;
    const hold = !this.state.hold;
    const connection = this.getConnection();
    if (!connection) {
      return;
    }

    this.setState({ hold });
    dispatch(toggleHold(connection, hold));
  };

  transferCall = (target, type) => {
    const { dispatch } = this.props;
    const connection = this.getConnection();
    if (!connection) {
      return;
    }

    dispatch(transferCall(connection, target, type));
    this.setState({
      transferTarget:     target,
      transferTargetType: type
    });
  };

  isCallActive = () => {
    const connection = this.getConnection();
    if (!connection) {
      return false;
    }

    return connection.status !== 'closed';
  };

  render() {
    const { agents, onlineAgentIds } = this.props;
    const onlineAgents = agents.filter(agent => onlineAgentIds.contains(agent.get('id')));
    const connection = this.getConnection();

    if (!connection) {
      return null;
    }

    return (
      <VoiceControls
        {...this.props}
        {...this.state}
        onlineAgents={onlineAgents}
        connection={this.getConnection()}
        endCall={this.endCall}
        toggleMute={this.toggleMute}
        toggleHold={this.toggleHold}
        sendDigits={this.sendDigits}
        onAddAgent={this.addAgent}
        onTransferCall={this.transferCall}
        onCancelInvite={this.cancelInvite}
      />
    );
  }
}

export default VoiceControlsContainer;
