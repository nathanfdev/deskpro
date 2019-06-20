import PropTypes from 'prop-types';
import React from 'react';
import moment from 'moment';
import classNames from 'classnames';
import EventEmitter from 'eventemitter2';
import ScrollArea from 'react-scrollbar';
import { WaitingFormat } from 'DeskPRO/Component/Timer';

const emitter = new EventEmitter();

class MissedCallsList extends React.Component {

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
        {records.toArray().map((record, index) =>
          <MissedCall
            {...this.props}
            key={index}
            record={record}
          />
        )}
      </ScrollArea>
    );
  }
}

class MissedCall extends React.Component {

  static propTypes = {
    record:               PropTypes.object,
    phoneCalls:           PropTypes.object,
    people:               PropTypes.object,
    createTicket:         PropTypes.func,
    callBack:             PropTypes.func,
    deleteRecording:      PropTypes.func,
    markListened:         PropTypes.func,
    openPerson:           PropTypes.func,
    outboundCallsEnabled: PropTypes.bool
  };

  static defaultProps = {
    createTicket:    () => {},
    callBack:        () => {},
    deleteRecording: () => {},
    markListened:    () => {},
    openPerson:      () => {}
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

  createTicket = (event) => {
    event.preventDefault();

    const { record, createTicket } = this.props;
    createTicket(record);
  };

  callBack = (event) => {
    event.preventDefault();

    const { record, phoneCalls, callBack, outboundCallsEnabled } = this.props;
    const phoneCall = phoneCalls.get(record.get('phone_call'));

    if (!outboundCallsEnabled) {
      return;
    }

    callBack(phoneCall);
  };

  deleteRecording = (event) => {
    event.preventDefault();

    const { record, deleteRecording } = this.props;
    deleteRecording(record);
  };

  playRecording = (event) => {
    event.preventDefault();

    const { record, markListened } = this.props;
    const { playing } = this.state;

    const recording = record.get('blob');
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

      markListened(record);
    } else {
      this.stopPlaying();
    }
  };

  openPerson = (event) => {
    event.preventDefault();

    const { record, phoneCalls, openPerson } = this.props;
    const phoneCall = phoneCalls.get(record.get('phone_call'));
    if (!phoneCall) {
      return;
    }

    openPerson(phoneCall.get('person'));
  };

  stopPlaying = () => {
    if (this.audio.readyState > 0) {
      this.audio.pause();
      this.audio.currentTime = 0;
    }

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
    const recording = record.get('blob');

    return (
      <div className="voice-menu-voicemail-record">
        <i
          className={classNames(
            playing ? 'pause' : 'play circle', 'icon voicemail-record-play-icon',
            { disabled: !recording, 'voicemail-new-record': !record.get('is_listened') }
          )}
          onClick={this.playRecording}
        />

        <div className="voicemail-record-content">
          <div className="voicemail-record-date-created">
            {moment(record.get('date_created')).fromNow()}
          </div>
          <div className="voicemail-record-duration">
            <WaitingFormat value={record.get('duration')} />
          </div>

          <div className="voicemail-person-name">
            {person && person.get('name')
              ? <a onClick={this.openPerson}>{person.get('name')} ({person.get('primary_email')})</a>
              : <span>Unknown user</span>
            }
          </div>
          <div>{phoneCall.get('external_number')}</div>

          <div className="voice-record-actions">
            <span className="voice-record-action">
              <a onClick={this.createTicket}>
                Create Ticket
              </a>
            </span>
            {outboundCallsEnabled &&
            <span className="voice-record-action">
              <a onClick={this.callBack}>
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
              <a onClick={this.deleteRecording}>
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

export default MissedCallsList;
