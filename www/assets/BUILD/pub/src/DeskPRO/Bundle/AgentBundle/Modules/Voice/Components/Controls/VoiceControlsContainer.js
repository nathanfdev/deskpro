import React, { PropTypes } from 'react';
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
    callId:         PropTypes.number,
    tabRef:         PropTypes.func,
    onEndCall:      PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      mute:               false,
      hold:               false,
      status:             'connecting',
      participants:       [],
      addTarget:          null,
      addTargetType:      null,
      transferTarget:     null,
      transferTargetType: null
    };
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
      const status = connection.status();
      if (status === 'open' && this.state.status !== 'active') {
        this.setState({
          status: 'connected'
        });
      } else if (status === 'closed') {
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

  onToggleMute = () => {
    const { dispatch } = this.props;
    const mute = !this.state.mute;
    const connection = this.getConnection();
    if (!connection) {
      return;
    }

    this.setState({ mute });
    dispatch(toggleMute(connection, mute));
  };

  onToggleHold = () => {
    const { dispatch } = this.props;
    const hold = !this.state.hold;
    const connection = this.getConnection();
    if (!connection) {
      return;
    }

    this.setState({ hold });
    dispatch(toggleHold(connection, hold));
  };

  onExternalSetHold = (event) => {
    const { callId } = this.props;
    if (callId !== parseInt(event.call_id, 10)) {
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
    if (event.agent_id === me.get('id')) {
      if (eventName === 'participant-join') {
        newState.status = 'active';
      } else if (eventName === 'participant-leave') {
        newState.status = 'closed';
      }
    }

    // update participant list
    if (['participant-join', 'participant-leave'].indexOf(eventName) !== -1) {
      newState.participants = event.agent_participants;

      const { addTarget, transferTarget } = this.state;

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

  onEndCall = () => {
    const { dispatch } = this.props;
    const connection = this.getConnection();
    if (!connection) {
      return;
    }

    dispatch(hangup(connection));
  };

  onAddAgent = (target, type) => {
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

  onTransferCall = (target, type) => {
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

  onCancelInvite = (target, type) => {
    const { dispatch, callId } = this.props;

    dispatch(cancelInvite(callId, target, type));
  };

  getConnection() {
    const { connections, callId } = this.props;
    return connections.filter(connection => parseInt(connection.message.CallId, 10) === parseInt(callId, 10)).first();
  }

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

    return (
      <VoiceControls
        {...this.props}
        {...this.state}
        onlineAgents={onlineAgents}
        connection={this.getConnection()}
        onEndCall={this.onEndCall}
        onMute={this.onToggleMute}
        onHold={this.onToggleHold}
        onAddAgent={this.onAddAgent}
        onTransferCall={this.onTransferCall}
        onCancelInvite={this.onCancelInvite}
      />
    );
  }
}

export default VoiceControlsContainer;
