import PropTypes from 'prop-types';
import React from 'react';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import Timer from 'DeskPRO/Component/Timer';
import CallFrom from './CallFrom';
import CallTarget from './CallTarget';
import IncomingCallAudio from './IncomingCallAudio';

class IncomingCall extends React.Component {

  static propTypes = {
    me:                    PropTypes.object,
    agents:                PropTypes.object,
    people:                PropTypes.object,
    queues:                PropTypes.object,
    incomingCall:          PropTypes.object,
    onAccept:              PropTypes.func,
    onDecline:             PropTypes.func,
    ringingVolume:         PropTypes.number,
    agentVoicemailTimeout: PropTypes.number
  };

  static defaultProps = {
    onAccept:  () => {},
    onDecline: () => {}
  };

  componentDidMount() {
    if (this.audio) {
      this.audio.playSound();
    }

    const { onDecline, agentVoicemailTimeout } = this.props;
    if (agentVoicemailTimeout) {
      setTimeout(() => { onDecline(); }, agentVoicemailTimeout * 1000);
    }
  }

  componentWillReceiveProps(newProps) {
    if (newProps.incomingCall && newProps.incomingCall.get('assigned_agent')) {
      if (this.audio) {
        this.audio.stopSound();
      }
    }
  }

  onAccept = () => {
    this.props.onAccept();
  };

  onDecline = (event) => {
    event.preventDefault();
    this.props.onDecline();
  };

  onSoundEnded = () => {
    this.audio.playSound();
  };

  renderAcceptCall() {
    const { me, agents, people, queues, incomingCall, ringingVolume } = this.props;

    let callType = 'Direct';
    if (incomingCall.get('call_type')) {
      callType = 'Invite';

      if (incomingCall.get('call_type') === 'transfer') {
        callType = 'Transfer';
      }

      if (incomingCall.get('from_agent_id')) {
        const agent = agents.get(incomingCall.get('from_agent_id'));
        if (agent) {
          callType += ` from ${agent.get('name')}`;
        }
      }
    } else if (incomingCall.get('queue_id')) {
      const queue = queues.get(incomingCall.get('queue_id'));
      if (queue) {
        callType = queue.get('name');
      }
    }

    return (
      <div className="incoming-call">
        <IncomingCallAudio
          ref={(c) => { this.audio = c; }}
          ringingVolume={ringingVolume}
          onSoundEnded={this.onSoundEnded}
        />
        <CallFrom incomingCall={incomingCall} people={people} />
        <div className="incoming-call-type">
          <div>
            <span>{callType}</span>
          </div>
        </div>
        <CallTarget target={{ type: 'agent', agent: me }} />

        <div className="buttons">
          <Button
            className="green call-button"
            onClick={this.onAccept}
          >
            <i className="icon call" />
            Answer
            <span className="waiting-time">
              <Timer format="waiting_time" />
            </span>
          </Button>
          <a
            className="ignore-button"
            href="#ignore"
            onClick={this.onDecline}
          >
            <i className="icon remove" />
            Ignore
          </a>
        </div>
      </div>
    );
  }

  renderAcceptedCall() {
    const { incomingCall, agents } = this.props;
    const agentId = incomingCall.get('assigned_agent');

    let agent;
    if (agentId) {
      agent = agents.get(agentId);
    }

    return (
      <div className="incoming-call">
        <div className="incoming-call-another-agent-message">
          Call was accepted by another agent
        </div>

        {agent
          ? <CallTarget target={{ type: 'agent', agent }} />
          : <div className="ui active centered inline loader incoming-call-avatar-loader" />
        }

        <div className="buttons">
          <a
            className="ignore-button"
            href="#ignore"
            onClick={this.onDecline}
          >
            <i className="icon remove" />
            Dismiss
          </a>
        </div>
      </div>
    );
  }

  render() {
    const { incomingCall } = this.props;
    return incomingCall && incomingCall.get('assigned_agent') ? this.renderAcceptedCall() : this.renderAcceptCall();
  }
}

export default IncomingCall;
