import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { voiceParticipantsSelector } from '../../Selectors/agents';
import VoiceControls from './VoiceControls';
import {
  hangup,
  toggleMute,
  toggleHold,
  warmAddAgent,
  warmTransferCall,
  coldTransferCall,
  cancelInvite,
  checkIsActive
} from '../../Actions/clientActions';
import { connectionsSelector } from '../../Selectors/client';
import { onlineAgentsSelector } from '../../../Agent/Selectors/agents';
import { allPhoneCallsSelector } from '../../Selectors/phoneCalls';

@connect(state => ({
  me:             meSelector(state),
  agents:         voiceParticipantsSelector(state),
  connections:    connectionsSelector(state),
  onlineAgentIds: onlineAgentsSelector(state),
  phoneCalls:     allPhoneCallsSelector(state)
}))
class VoiceControlsContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    me:             PropTypes.object,
    agents:         PropTypes.object,
    onlineAgentIds: PropTypes.object,
    connections:    PropTypes.object,
    ticketId:       PropTypes.number,
    baseId:         PropTypes.string,
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
      status:             null,
      addTarget:          null,
      addTargetType:      null,
      transferTarget:     null,
      transferTargetType: null,
      hold:               false,
      participants:       [],
      inviteError:        null
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

  componentWillUpdate() {
    const { addTarget, transferTarget, participants } = this.state;
    const newState = {};

    // update participant list
    let update = false;
    if (addTarget && participants.indexOf(addTarget.get('id')) !== -1) {
      newState.addTarget     = null;
      newState.addTargetType = null;

      update = true;
    }
    if (transferTarget && participants.indexOf(transferTarget.get('id')) !== -1) {
      newState.transferTarget     = null;
      newState.transferTargetType = null;

      update = true;
    }

    if (update) {
      this.setState(newState);
    }
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

  addAgent = (target, type) => {
    const { dispatch } = this.props;
    const connection = this.getConnection();
    if (!connection) {
      return null;
    }

    const promise = dispatch(warmAddAgent(connection, target));
    promise.error((error) => {
      this.setState({
        addTarget:     null,
        addTargetType: null,
        inviteError:   error.message
      });
    });

    this.setState({
      addTarget:     target,
      addTargetType: type,
      inviteError:   null
    });

    return promise;
  };

  cancelInvite = (target, type) => {
    const { dispatch } = this.props;
    const connection = this.getConnection();

    const promise = dispatch(cancelInvite(connection.callId, target, type));
    promise.then(() => {
      this.setState({
        addTarget:          null,
        addTargetType:      null,
        transferTarget:     null,
        transferTargetType: null,
        inviteError:        null
      });
    });
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

  transferCall = (target, type) => {
    const { dispatch } = this.props;
    const connection = this.getConnection();
    if (!connection) {
      return null;
    }

    let promise;
    if (type === 'cold') {
      promise = dispatch(coldTransferCall(connection, target, type));
    } else {
      promise = dispatch(warmTransferCall(connection, target, type));
    }

    promise.error((error) => {
      this.setState({
        transferTarget:     null,
        transferTargetType: null,
        inviteError:        error.message
      });
    });

    this.setState({
      transferTarget:     target,
      transferTargetType: type,
      inviteError:        null
    });

    return promise;
  };

  isCallActive = () => {
    const connection = this.getConnection();
    if (!connection) {
      return false;
    }

    return connection.status !== 'closed';
  };

  render() {
    const { agents, onlineAgentIds, baseId } = this.props;
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
        connection={connection}
        endCall={this.endCall}
        toggleMute={this.toggleMute}
        toggleHold={this.toggleHold}
        sendDigits={this.sendDigits}
        onAddAgent={this.addAgent}
        transferCall={this.transferCall}
        cancelInvite={this.cancelInvite}
        baseId={baseId}
      />
    );
  }
}

export default VoiceControlsContainer;
