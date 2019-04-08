import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { voiceAgentsSelector } from '../../Selectors/agents';
import VoiceControls from './VoiceControls';
import {
  hangup,
  toggleMute,
  toggleHold,
  warmAddAgent,
  warmTransferToAgent,
  coldTransferToAgent,
  coldTransferToQueue,
  coldTransferToAutoAttendant,
  cancelInvite,
  checkIsActive
} from '../../Actions/clientActions';
import { agentVoicemailTimeoutSelector, busyAgentsSelector, connectionsSelector } from '../../Selectors/client';
import { onlineAgentsSelector } from '../../../Agent/Selectors/agents';
import { allQueuesSelector } from '../../Selectors/queue';
import { allAutoAttendantsSelector } from '../../Selectors/autoAttendants';
import { allPhoneCallsSelector } from '../../Selectors/phoneCalls';

@connect(state => ({
  me:                    meSelector(state),
  agents:                voiceAgentsSelector(state),
  queues:                allQueuesSelector(state),
  autoAttendants:        allAutoAttendantsSelector(state),
  connections:           connectionsSelector(state),
  onlineAgentIds:        onlineAgentsSelector(state),
  phoneCalls:            allPhoneCallsSelector(state),
  agentVoicemailTimeout: agentVoicemailTimeoutSelector(state),
  busyAgents:            busyAgentsSelector(state)
}))
class VoiceControlsContainer extends React.Component {

  static propTypes = {
    dispatch:              PropTypes.func,
    me:                    PropTypes.object,
    agents:                PropTypes.object,
    onlineAgentIds:        PropTypes.object,
    connections:           PropTypes.object,
    ticketId:              PropTypes.number,
    baseId:                PropTypes.string,
    tabRef:                PropTypes.func,
    onEndCall:             PropTypes.func,
    phoneCalls:            PropTypes.object,
    agentVoicemailTimeout: PropTypes.number,
  };

  static defaultProps = {
    onEndCall: () => {}
  };

  constructor(props) {
    super(props);
    this.state = {
      mute:         false,
      status:       null,
      hold:         false,
      participants: [],
      inviteError:  null
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
    const { dispatch, tabRef, onEndCall } = this.props;
    tabRef({
      isCallActive: this.isCallActive,
      endCall:      this.endCall
    });

    this.interval = setInterval(() => {
      const { me } = this.props;
      const { status } = this.state;
      const connection = this.getConnection();

      let connectionStatus;
      if (!connection) {
        connectionStatus = 'closed';
      } else {
        if (connection.status) {
          connectionStatus = connection.status();
        }
        if (connection.getCallUUID && connection.getCallUUID()) {
          connectionStatus = 'open';
        }
      }

      if (connectionStatus === 'open') {
        if (status !== 'active' && this.state.participants.indexOf(me.get('id') !== -1)) {
          this.setState({
            status: 'active'
          });

          const promise = dispatch(checkIsActive(connection.callId));
          promise.success(({ data }) => {
            if (!data.is_active) {
              dispatch(hangup(connection));
            }
          });
        } else if (status !== 'connected' && status !== 'active') {
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

    const messageBroker = window.DeskPRO_Window.getMessageBroker();
    messageBroker.addMessageListener('agent.voice.conference.status', (event) => {
      const connection = this.getConnection();
      if (!connection || parseInt(connection.callId, 10) !== parseInt(event.phone_call.id, 10)) {
        return;
      }

      this.setState({
        participants: event.agent_participants,
        hold:         !!event.hold
      });
    });
    messageBroker.addMessageListener('agent.voice.conference.hold', (event) => {
      const connection = this.getConnection();
      if (!connection || parseInt(connection.callId, 10) !== parseInt(event.call_id, 10)) {
        return;
      }

      this.setState({
        hold: !!event.hold
      });
    });
  }

  componentWillUnmount() {
    this.props.tabRef(null);
    clearInterval(this.interval);
  }

  getConnection() {
    const { connections, ticketId } = this.props;
    return connections
      .filter(connection => parseInt(connection.ticketId, 10) === parseInt(ticketId, 10))
      .first();
  }

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

    // twilio
    if (connection.sendDigits) {
      connection.sendDigits(`${digit}`);
    }

    // plivo
    if (connection.sendDtmf) {
      connection.sendDtmf(`${digit}`);
    }
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
    const { hold } = this.state;
    const connection = this.getConnection();

    const promise = connection ? dispatch(toggleHold(connection.callId, !hold)) : null;
    if (promise) {
      promise.success(() => {
        // update hold status right away
        // don't wait for a browser notification
        this.setState({
          hold: !hold
        });
      });
    }

    return promise;
  };

  sendInvite = method => (target) => {
    const { dispatch, agentVoicemailTimeout } = this.props;
    const connection = this.getConnection();
    if (!connection) {
      return;
    }

    const promise = dispatch(method(connection, target));
    promise.success(() => {
      setTimeout(() => {
        if (this.state.target) {
          this.cancelInvite('Call was canceled by timeout');
        }
      }, agentVoicemailTimeout * 1000);
    });
    promise.error((error) => {
      this.setState({
        target:      null,
        inviteError: error.message
      });
    });

    this.setState({
      target,
      inviteError: null
    });
  };

  cancelInvite = (inviteError = null) => {
    const { dispatch } = this.props;
    const connection = this.getConnection();
    if (!connection) {
      return;
    }

    const promise = dispatch(cancelInvite(connection.callId, this.state.target));
    promise.then(() => {
      this.setState({
        target: null,
        inviteError
      });
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
    const { me, agents, onlineAgentIds, baseId, phoneCalls } = this.props;
    const onlineAgents = agents.filter(agent => onlineAgentIds.contains(agent.get('id')) && agent !== me);
    const connection = this.getConnection();

    if (!connection) {
      return null;
    }

    return (
      <VoiceControls
        {...this.props}
        {...this.state}
        phoneCall={phoneCalls.get(connection.callId)}
        onlineAgents={onlineAgents}
        connection={connection}
        endCall={this.endCall}
        toggleMute={this.toggleMute}
        toggleHold={this.toggleHold}
        sendDigits={this.sendDigits}
        warmAddAgent={this.sendInvite(warmAddAgent)}
        warmTransferToAgent={this.sendInvite(warmTransferToAgent)}
        coldTransferToAgent={this.sendInvite(coldTransferToAgent)}
        coldTransferToQueue={this.sendInvite(coldTransferToQueue)}
        coldTransferToAutoAttendant={this.sendInvite(coldTransferToAutoAttendant)}
        cancelInvite={this.cancelInvite}
        baseId={baseId}
      />
    );
  }
}

export default VoiceControlsContainer;
