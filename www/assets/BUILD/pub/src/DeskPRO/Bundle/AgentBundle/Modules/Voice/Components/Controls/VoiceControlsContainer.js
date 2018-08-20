import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { voiceParticipantsSelector } from '../../Selectors/agents';
import VoiceControls from './VoiceControls';
import { hangup, toggleMute, toggleHold, addAgent, transferCall, cancelInvite } from '../../Actions/clientActions';
import { connectionsSelector, connectionStatesSelector } from '../../Selectors/client';
import { onlineAgentsSelector } from '../../../Agent/Selectors/agents';

@connect(state => ({
  me:               meSelector(state),
  agents:           voiceParticipantsSelector(state),
  connections:      connectionsSelector(state),
  onlineAgentIds:   onlineAgentsSelector(state),
  connectionStates: connectionStatesSelector(state)
}))
class VoiceControlsContainer extends React.Component {

  static propTypes = {
    dispatch:         PropTypes.func,
    me:               PropTypes.object,
    agents:           PropTypes.object,
    onlineAgentIds:   PropTypes.object,
    connections:      PropTypes.object,
    ticketId:         PropTypes.number,
    baseId:           PropTypes.string,
    tabRef:           PropTypes.func,
    onEndCall:        PropTypes.func,
    connectionStates: PropTypes.object,
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
      endCall:      this.endCall
    });

    this.interval = setInterval(() => {
      const connection = this.getConnection();

      const connectionStatus = connection ? connection.status() : 'closed';
      const connectionState = this.getConnectionState();
      const { me } = this.props;
      const { status } = this.state;

      if (connectionStatus === 'open') {
        if (status !== 'active'
          && (connectionState.participants.contains(me.get('id')) || connection.message.Outbound)
        ) {
          this.setState({
            status: 'active'
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
  }

  componentWillUpdate() {
    const { addTarget, transferTarget } = this.state;
    const connectionState = this.getConnectionState();
    const participants = connectionState.participants;
    const newState = {};

    // update participant list
    let update = false;
    if (addTarget && participants.contains(addTarget.get('id'))) {
      newState.addTarget     = null;
      newState.addTargetType = null;

      update = true;
    }
    if (transferTarget && participants.contains(transferTarget.get('id'))) {
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
      .filter(connection => parseInt(connection.message.TicketId, 10) === parseInt(ticketId, 10))
      .first();
  }

  getConnectionState() {
    const { connectionStates } = this.props;
    const connection = this.getConnection();

    let connectionState;
    if (connection) {
      connectionState = connectionStates.get(connection.message.CallId);
    }
    if (!connectionState) {
      connectionState = Immutable.fromJS({
        hold:         false,
        participants: []
      });
    }

    return connectionState.toJS();
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

    const promise = dispatch(cancelInvite(connection.message.CallId, target, type));
    promise.then(() => {
      this.setState({
        addTarget:          null,
        addTargetType:      null,
        transferTarget:     null,
        transferTargetType: null
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
    const connection = this.getConnection();
    const connectionState = this.getConnectionState();
    if (!connection) {
      return;
    }

    dispatch(toggleHold(connection, !connectionState.hold));
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
    const { agents, onlineAgentIds, baseId } = this.props;
    const onlineAgents = agents.filter(agent => onlineAgentIds.contains(agent.get('id')));
    const connection = this.getConnection();
    const connectionState = this.getConnectionState();

    if (!connection) {
      return null;
    }

    return (
      <VoiceControls
        {...this.props}
        {...this.state}
        {...connectionState}
        onlineAgents={onlineAgents}
        connection={connection}
        endCall={this.endCall}
        toggleMute={this.toggleMute}
        toggleHold={this.toggleHold}
        sendDigits={this.sendDigits}
        onAddAgent={this.addAgent}
        onTransferCall={this.transferCall}
        onCancelInvite={this.cancelInvite}
        baseId={baseId}
      />
    );
  }
}

export default VoiceControlsContainer;
