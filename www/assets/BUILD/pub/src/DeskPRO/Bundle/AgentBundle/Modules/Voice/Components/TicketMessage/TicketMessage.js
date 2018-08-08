import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import Immutable from 'immutable';
import moment from 'moment';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import MediaControls from 'DeskPRO/Component/MediaControls';
import Duration from 'DeskPRO/Component/Duration';
import classNames from 'classnames';
import Avatar from '../Common/Avatar';

class TicketMessage extends React.Component {

  static propTypes = {
    people:               PropTypes.object,
    numbers:              PropTypes.object,
    message:              PropTypes.object,
    phoneCall:            PropTypes.object,
    connection:           PropTypes.object,
    transcript:           PropTypes.string,
    onCall:               PropTypes.func,
    outboundCallsEnabled: PropTypes.bool,
    dateCreatedFormatted: PropTypes.string
  };

  static defaultProps = {
    onCall:         () => {},
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
    const { message = {}, phoneCall = Immutable.fromJS({}), numbers, people, connection } = this.props;
    const { transcript, outboundCallsEnabled, dateCreatedFormatted } = this.props;
    const { onCall } = this.props;
    const { transcriptExpanded, logExpanded } = this.state;
    const participants = phoneCall.get('participants') || [];
    const recording = phoneCall.get('recording');
    const number = numbers.get(phoneCall.get('number')) || Immutable.fromJS({});

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
              <FormattedMessage
                id={phoneCall.get('type') === 'outbound'
                  ? 'agent.voice.outgoing_call_title'
                  : 'agent.voice.incoming_call_title'}
              />
            </span>
            <span className="voice-ticket-message-date">
              <time
                className="timeago with-stickytip timeago-auto-update with-timeago dp-stickytip-init"
                dateTime={message.date_created}
                title={dateCreatedFormatted}
              />
            </span>
          </div>
          {connection
            ? <div className="voice-ticket-message-controls">
                Call in progress
              </div>
            : <div className="voice-ticket-message-controls">
              {recording && <MediaControls recording={recording} />}
              {!recording && phoneCall.get('recording_is_downloading') ? 'Call recording is being processed. It will be available for download in a few minutes.' : ''}
              {outboundCallsEnabled &&
              <Button className="basic call-button" onClick={onCall}>
                <i className="icon call" /> Call {phoneCall.get('external_number')}
              </Button>}
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
            <table>
              {phoneCall.get('phone_call_logs').map((log, index) => {
                const person   = people.get(log.get('person')) || Immutable.fromJS({});
                const logDate  = moment(log.get('date_created'));
                const callDate = moment(phoneCall.get('date_created'));
                const duration = logDate.unix() - callDate.unix();

                return (
                  <tr key={index}>
                    <td>
                      [<Duration value={duration} />]
                    </td>
                    <td>
                      <FormattedMessage
                        id={`agent.voice.${log.get('action_type').replace(/\.+/, '_')}`}
                        values={{
                          number:           phoneCall.get('external_number'),
                          to_number:        number.get('nickname') || number.get('number'),
                          person_name:      person.get('first_name') || '',
                          person_email:     person.get('primary_email') || '',
                          key:              log.getIn(['details', 'Digits']) || '',
                          target_name:      log.getIn(['details', 'target_name']) || 'Unknown',
                          forwarded_number: log.getIn(['details', 'forwarded_number']) || ''
                        }}
                      />
                    </td>
                  </tr>
                );
              })}
            </table>}
          </div>
        </div>
      </div>
    );
  }
}

export default TicketMessage;
