import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import Immutable from 'immutable';
import moment from 'moment';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import MediaControls from 'DeskPRO/Component/MediaControls';
import Duration from 'DeskPRO/Component/Duration';
import classNames from 'classnames';
import PopUp from 'DeskPRO/Component/Semantic/PopUp/PopUp';
import { deleteRecord } from '../../Actions/clientActions';
import Avatar from '../Common/Avatar';
import MessagePhoneNumber from './MessagePhoneNumber';
import { TicketMessageMenu } from './TicketMessageMenu';

class TicketMessage extends React.Component {

  static propTypes = {
    people:               PropTypes.object,
    numbers:              PropTypes.object,
    message:              PropTypes.object,
    phoneCall:            PropTypes.object,
    connection:           PropTypes.object,
    transcript:           PropTypes.string,
    outboundCallsEnabled: PropTypes.bool,
    dateCreatedFormatted: PropTypes.string,
    openTarget:           PropTypes.func,
    openDialpad:          PropTypes.func,
    me:                   PropTypes.object,
    elid:                 PropTypes.string,
    dispatch:             PropTypes.func,
    canEditMessage:       PropTypes.bool
  };

  static defaultProps = {
    openDialpad:    () => {},
    canEditMessage: false
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

  openMenu = () => {
    if (this.popup) {
      this.popup.openPopup();
    }
  };

  deleteRecord = (phoneCallId) => {
    this.props.dispatch(deleteRecord(phoneCallId));
  };

  render() {
    const { message = {}, phoneCall = Immutable.fromJS({}), numbers, people, connection } = this.props;
    const { transcript, outboundCallsEnabled, dateCreatedFormatted, canEditMessage } = this.props;
    const { openDialpad, me, openTarget } = this.props;
    const { transcriptExpanded, logExpanded } = this.state;
    const participants = phoneCall.get('participants') || [];
    const recordings = phoneCall.get('recordings');
    const recordingsEnabled = recordings.filter(recording => recording.get('blob'));
    const recordingsDeleted = recordings.filter(recording => recording.get('is_deleted'));
    const number = numbers.get(phoneCall.get('number')) || Immutable.fromJS({});

    return (
      <div className="voice-ticket-message">
        <div className="voice-ticket-message-participants" style={{ width: Math.ceil(participants.size / 3) * 45 }}>
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
            {canEditMessage &&
            <span className="voice-ticket-message-edit-menu" onClick={this.openMenu}>
              <i className="fas fa-cog" />
              <PopUp
                ref={(c) => { this.popup = c; }}
                positionMy="right top"
                positionAt="right+20 bottom-15"
                zIndex={99999}
                className="voice-ticket-message-edit-menu-popup"
                content={
                  <TicketMessageMenu
                    phoneCall={phoneCall}
                    deleteRecord={this.deleteRecord}
                  />
                }
              />
            </span>}
            <span className="voice-ticket-message-date">
              <time
                data-stickytip-target={`#${this.props.elid}`}
                className="timeago with-stickytip timeago-auto-update with-timeago"
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
              {outboundCallsEnabled &&
              <Button className="basic call-button" onClick={openDialpad}>
                <i className="icon call" /> Call {phoneCall.get('external_number')}
              </Button>}
              {recordingsEnabled.size > 0 && recordingsEnabled.map(recording => <MediaControls key={`recording_${recording.get('blob').get('blob_id')}`} recording={recording.get('blob')} />)}
              {recordings.size > 0 && !recordingsDeleted.size && !recordingsEnabled.size ? 'Call recording is being processed. It will be available for download in a few minutes.' : ''}
              {recordingsDeleted.size > 0 && !recordingsEnabled.size && 'This call recording has been deleted.'}
              {!recordings.size ? 'This call was not recorded.' : ''}
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
              <tbody>
                {phoneCall.get('phone_call_logs').map((log, index) => {
                  const person   = people.get(log.get('person')) || Immutable.fromJS({});
                  const logDate  = moment(log.get('date_created'));
                  const callDate = moment(phoneCall.get('date_created'));
                  const duration = logDate.unix() - callDate.unix();

                  let target = 'Unknown';
                  if (log.getIn(['details', 'target'])) {
                    if (me.get('can_admin')) {
                      target = (
                        <a onClick={() => openTarget(log.getIn(['details', 'target']))}>
                          {log.getIn(['details', 'target', 'name'])}
                        </a>
                      );
                    } else {
                      target = log.getIn(['details', 'target', 'name']);
                    }
                  }

                  return (
                    <tr key={index}>
                      <td>
                        [<Duration value={duration} />]
                      </td>
                      <td>
                        <FormattedMessage
                          id={`agent.voice.${log.get('action_type').replace(/\.+/, '_')}`}
                          values={{
                            number: (
                              <MessagePhoneNumber number={phoneCall.get('external_number')}>
                                {phoneCall.get('external_number')}
                              </MessagePhoneNumber>
                            ),
                            person: (
                              <a data-route={`person:/agent/people/${person.get('id')}`}>
                                {person.get('name')} {person.get('primary_email') ? `( ${person.get('primary_email')} )` : ''}
                              </a>
                            ),
                            to_number:        number.get('nickname') || number.get('number'),
                            key:              log.getIn(['details', 'Digits']) || '',
                            target,
                            forwarded_number: log.getIn(['details', 'forwarded_number']) || ''
                          }}
                        />
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>}
          </div>
        </div>
      </div>
    );
  }
}

export default TicketMessage;
