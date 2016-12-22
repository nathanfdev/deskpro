import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import classNames from 'classnames';
import { Range } from 'DeskPRO/Component/Semantic/Form';
import Avatar from '../Common/Avatar';

class TicketMessage extends React.Component {

  static propTypes = {
    people:         PropTypes.object,
    message:        PropTypes.object,
    phoneCall:      PropTypes.object,
    connection:     PropTypes.object,
    transcript:     PropTypes.string,
    onCall:         PropTypes.func,
    onPlay:         PropTypes.func,
    onMove:         PropTypes.func,
    onStepBackward: PropTypes.func
  };

  static defaultProps = {
    onCall:         () => {},
    onPlay:         () => {},
    onMove:         () => {},
    onStepBackward: () => {},
    onOpenSettings: () => {}
  };

  constructor(props) {
    super(props);
    this.state = {
      transcriptExpanded: false,
      logExpanded:        false
    };
  }

  onToggleTranscript = () => {
    const { transcriptExpanded } = this.state;
    this.setState({
      transcriptExpanded: !transcriptExpanded
    });
  };

  onToggleLog = () => {
    const { logExpanded } = this.state;
    this.setState({
      logExpanded: !logExpanded
    });
  };

  render() {
    const { message = {}, phoneCall = Immutable.fromJS({}), people, connection } = this.props;
    const { transcript } = this.props;
    const { onCall, onPlay, onMove, onStepBackward } = this.props;
    const { transcriptExpanded, logExpanded } = this.state;
    const participants = phoneCall.get('participants') || [];

    return (
      <div className="voice-ticket-message">
        <div className="voice-ticket-message-participants">
          {participants.map((participant, index) =>
            <Avatar
              key={index}
              person={people.get(participant.get('person'))}
              size={40}
            />
          )}
        </div>
        <div className="voice-ticket-message-body">
          <div className="voice-ticket-message-header">
            <span className="voice-ticket-message-id">
              #{message.id}
            </span>
            <span className="voice-ticket-message-title">
              <i className="icon call" />
              {agentPhrases.get('agent.voice.incoming_call_title')}
            </span>
            <span className="voice-ticket-message-date">
              <time
                className="timeago with-stickytip timeago-auto-update with-timeago dp-stickytip-init"
                dateTime={message.date_created}
                title=""
              />
            </span>
          </div>
          {connection
            ? <div className="voice-ticket-message-controls">
                Call in progress
              </div>
            : <div className="voice-ticket-message-controls">
              <Button className="basic icon disabled" onClick={onStepBackward}>
                <i className="icon step backward" />
              </Button>
              <Button className="basic icon disabled" onClick={onPlay}>
                <i className="icon play" />
              </Button>
              <div className="voice-ticket-message-timeline">
                <Range onChange={onMove} />
              </div>
              <span className="voice-ticket-message-time">
                00.41 / 02.13
              </span>
              <span className="voice-ticket-message-size">
                <i className="icon download" /> 1.2MB
              </span>
              <Button className="basic call-button" onClick={onCall}>
                <i className="icon call" /> Call {phoneCall.get('from_number')}
              </Button>
            </div>}
          {transcript &&
            <div className="voice-ticket-message-transcript">
              {!transcriptExpanded &&
                <span className="voice-ticket-message-section-title">
                  Transcript
                </span>}
              <i
                className={classNames(
                  'voice-ticket-message-expand-button icon angle',
                  transcriptExpanded ? 'up' : 'down'
                )}
                onClick={this.onToggleTranscript}
              />
              {transcriptExpanded &&
                <span className="voice-ticket-message-transcript-text">
                  {transcript}
                </span>}
            </div>}
          <div className="voice-ticket-message-log">
            {!logExpanded &&
            <span className="voice-ticket-message-section-title">
                Log
              </span>}
            <i
              className={classNames(
                'voice-ticket-message-expand-button icon angle',
                logExpanded ? 'up' : 'down'
              )}
              onClick={this.onToggleLog}
            />
            {logExpanded &&
            <span className="voice-ticket-message-transcript-text">
              <ul>
                {phoneCall.get('phone_call_logs').map((log, index) => {
                  const person = people.get(log.get('person')) || Immutable.fromJS({});

                  return (
                    <li key={index}>
                      {agentPhrases.get(`agent.voice.${log.get('action_type').replace(/\.+/, '_')}`, {
                        '{number}':       phoneCall.get('from_number'),
                        '{person_name}':  person.get('first_name') || '',
                        '{person_email}': person.get('primary_email') || ''
                      })}
                    </li>
                  );
                })}
              </ul>
            </span>}
          </div>
        </div>
      </div>
    );
  }
}

export default TicketMessage;
