import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import Timer from 'DeskPRO/Component/Timer';
import CallFrom from './CallFrom';
import CallTarget from './CallTarget';
import '../../../../../Resources/sounds/incoming-call.mp3';
import '../../../../../Resources/sounds/incoming-call.ogg';
import '../../../../../Resources/sounds/incoming-call.wav';

class IncomingCall extends React.Component {

  static propTypes = {
    me:           PropTypes.object,
    agents:       PropTypes.object,
    incomingCall: PropTypes.object,
    onAccept:     PropTypes.func,
    onDecline:    PropTypes.func
  };

  static defaultProps = {
    onAccept:  () => {},
    onDecline: () => {}
  };

  componentDidMount() {
    this.sound.addEventListener('ended', this.onSoundEnded);
    this.sound.play();
  }

  componentWillUnmount() {
    this.sound.removeEventListener('ended', this.onSoundEnded);
    this.sound.pause();
  }

  onAccept = (event) => {
    event.preventDefault();
    this.props.onAccept();
  };

  onDecline = (event) => {
    event.preventDefault();
    this.props.onDecline();
  };

  onSoundEnded = () => {
    this.sound.play();
  };

  render() {
    const { me, agents, incomingCall } = this.props;
    const soundsPath = `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/sounds`;

    let callType = 'Direct';
    if (incomingCall instanceof Immutable.Map && incomingCall.get('call_id')) {
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
    }

    return (
      <div className="incoming-call">
        <audio ref={(c) => { this.sound = c; }} preload="preload">
          <source src={`${soundsPath}/incoming-call.mp3`} />
          <source src={`${soundsPath}/incoming-call.ogg`} />
          <source src={`${soundsPath}/incoming-call.wav`} />
        </audio>
        <CallFrom incomingCall={incomingCall} />
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
}

export default IncomingCall;
