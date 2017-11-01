import PropTypes from 'prop-types';
import React from 'react';
import moment from 'moment';
import classNames from 'classnames';
import EventEmitter from 'eventemitter2';
import ScrollArea from 'react-scrollbar';
import { WaitingFormat } from 'DeskPRO/Component/Timer';

const emitter = new EventEmitter();

class VoicemailList extends React.Component {

  static propTypes = {
    records: PropTypes.object
  };

  render() {
    const { records } = this.props;
    if (!records.size) {
      return (
        <div className="voice-menu-voicemail-list">
          <div className="voice-menu-voicemail-list-empty-message">
            You have no voicemail messages.
          </div>
        </div>
      );
    }

    return (
      <ScrollArea className="voice-menu-voicemail-list">
        {records.map((record, index) =>
          <VoicemailRecord
            {...this.props}
            key={index}
            record={record}
          />
        )}
      </ScrollArea>
    );
  }
}

class VoicemailRecord extends React.Component {

  static propTypes = {
    record:               PropTypes.object,
    phoneCalls:           PropTypes.object,
    people:               PropTypes.object,
    onCreateTicket:       PropTypes.func,
    onCallback:           PropTypes.func,
    onDelete:             PropTypes.func,
    onMarkListened:       PropTypes.func,
    onOpenPerson:         PropTypes.func,
    outboundCallsEnabled: PropTypes.bool
  };

  static defaultProps = {
    onCreateTicket: () => {},
    onCallback:     () => {},
    onDelete:       () => {},
    onMarkListened: () => {},
    onOpenPerson:   () => {}
  };

  constructor(props) {
    super(props);
    this.state = {
      playing: false
    };
  }

  componentDidMount() {
    this.audio.addEventListener('ended', this.stopPlaying);
    emitter.on('stopPlaying', this.stopPlaying);
  }

  componentWillUnmount() {
    this.stopPlaying();
    this.audio.removeEventListener('ended', this.stopPlaying);
    emitter.off('stopPlaying', this.stopPlaying);
  }

  onCreateTicket = (event) => {
    event.preventDefault();

    const { record, onCreateTicket } = this.props;
    onCreateTicket(record);
  };

  onCallback = (event) => {
    event.preventDefault();

    const { record, phoneCalls, onCallback, outboundCallsEnabled } = this.props;
    const phoneCall = phoneCalls.get(record.get('phone_call'));

    if (!outboundCallsEnabled) {
      return;
    }

    onCallback(phoneCall);
  };

  onDelete = (event) => {
    event.preventDefault();

    const { record, onDelete } = this.props;
    onDelete(record);
  };

  onPlay = (event) => {
    event.preventDefault();

    const { record, phoneCalls, onMarkListened } = this.props;
    const { playing } = this.state;

    const phoneCall = phoneCalls.get(record.get('phone_call'));
    if (!phoneCall) {
      return;
    }

    const recording = phoneCall.get('recording');
    if (!recording) {
      return;
    }

    emitter.emit('stopPlaying');

    if (!playing) {
      this.audio.src = recording.get('download_url');
      this.audio.play();
      this.setState({
        playing: true
      });

      onMarkListened(record);
    } else {
      this.audio.pause();
      this.audio.currentTime = 0;
      this.setState({
        playing: false
      });
    }
  };

  onOpenPerson = (event) => {
    event.preventDefault();

    const { record, phoneCalls, onOpenPerson } = this.props;
    const phoneCall = phoneCalls.get(record.get('phone_call'));
    if (!phoneCall) {
      return;
    }

    onOpenPerson(phoneCall.get('person'));
  };

  stopPlaying = () => {
    this.audio.pause();
    this.audio.currentTime = 0;
    this.setState({
      playing: false
    });
  };

  renderContent() {
    const { record, phoneCalls, people, outboundCallsEnabled } = this.props;
    const { playing } = this.state;
    const phoneCall = phoneCalls.get(record.get('phone_call'));

    if (!phoneCall) {
      return null;
    }

    const person = people.get(phoneCall.get('person'));
    const recording = phoneCall.get('recording');

    return (
      <div className="voice-menu-voicemail-record">
        <i
          className={classNames(
            playing ? 'pause' : 'play circle', 'icon voicemail-record-play-icon',
            { disabled: !recording, 'voicemail-new-record': !record.get('is_listened') }
          )}
          onClick={this.onPlay}
        />

        <div className="voicemail-record-content">
          <div className="voicemail-record-date-created">
            {moment(record.get('date_created')).fromNow()}
          </div>
          <div className="voicemail-record-duration">
            <WaitingFormat value={phoneCall.get('duration')} />
          </div>

          <div className="voicemail-person-name">
            {person && person.get('name')
              ? <a onClick={this.onOpenPerson}>{person.get('name')} ({person.get('primary_email')})</a>
              : <span>Unknown user</span>
            }
          </div>
          <div>{phoneCall.get('external_number')}</div>

          <div className="voice-record-actions">
            <span className="voice-record-action">
              <a onClick={this.onCreateTicket}>
                Create Ticket
              </a>
            </span>
            {outboundCallsEnabled &&
            <span className="voice-record-action">
              <a onClick={this.onCallback}>
                Call Back
              </a>
            </span>}
            {recording &&
            <span className="voice-record-action">
              <a
                className="media-size"
                href={`${recording.get('download_url')}?dl=1`}
                target="_blank"
                rel="noopener noreferrer"
              >
                Download
              </a>
            </span>}
            <span className="voice-record-action">
              <a onClick={this.onDelete}>
                Delete
              </a>
            </span>
          </div>
        </div>
      </div>
    );
  }

  render() {
    return (
      <div>
        <audio ref={(c) => { this.audio = c; }} />
        {this.renderContent()}
      </div>
    );
  }
}

export default VoicemailList;
