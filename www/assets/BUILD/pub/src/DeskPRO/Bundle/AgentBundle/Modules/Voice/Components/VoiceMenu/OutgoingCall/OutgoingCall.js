import PropTypes from 'prop-types';
import React from 'react';
import Avatar from '../../Common/Avatar';
import OutgoingCallAudio from './OutgoingCallAudio';

class OutgoingCall extends React.Component {

  static propTypes = {
    me:            PropTypes.object,
    people:        PropTypes.object,
    outgoingCall:  PropTypes.object,
    hangup:        PropTypes.func,
    init:          PropTypes.bool,
    ringingVolume: PropTypes.number
  };

  static defaultProps = {
    hangup: () => {}
  };

  componentDidMount() {
    this.audio.playSound();
  }

  hangup = (event) => {
    event.preventDefault();
    const { hangup, init } = this.props;
    if (init) {
      hangup();
    }
  };

  render() {
    const { init, me, people, outgoingCall, ringingVolume } = this.props;
    const person = people.get(outgoingCall.getIn(['phoneCall', 'person']));

    return (
      <div className="outgoing-call">
        <OutgoingCallAudio
          ref={(c) => { this.audio = c; }}
          ringingVolume={ringingVolume}
          loop
        />
        <CallFrom
          agent={me}
          number={outgoingCall.get('callFrom')}
        />
        <div className="incoming-call-type">
          <div>
            <span>Direct</span>
          </div>
        </div>
        <CallTo
          number={outgoingCall.get('callTo')}
          person={person}
        />
        <div className="buttons">
          {init ?
            <a
              className="ignore-button"
              href="#ignore"
              onClick={this.hangup}
            >
              <i className="icon remove" />
              Cancel
            </a>
            : <div className="ui active inline loader" />}
        </div>
      </div>
    );
  }
}

class CallFrom extends React.Component {

  static propTypes = {
    agent:  PropTypes.object,
    number: PropTypes.string
  };

  render() {
    const { agent, number } = this.props;

    return (
      <div className="call-from">
        <div className="call-from-number">
          {number ? number.get('number') : 'Unknown number'}
        </div>
        <Avatar person={agent} size={60} />
        <div className="call-from-name">
          {agent && agent.get('name')}
        </div>
      </div>
    );
  }
}

class CallTo extends React.Component {

  static propTypes = {
    person: PropTypes.object,
    number: PropTypes.string
  };

  render() {
    const { person, number } = this.props;

    return (
      <div className="call-to">
        <div className="call-from-number">
          {number}
        </div>
        <Avatar person={person} size={60} />
        <div className="call-to-name">
          {person && person.get('name')}
        </div>
      </div>
    );
  }
}

export default OutgoingCall;
