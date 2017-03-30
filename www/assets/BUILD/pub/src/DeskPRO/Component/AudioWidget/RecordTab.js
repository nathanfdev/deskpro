import React from 'react';
import classNames from 'classnames';
import RecordRTC from 'recordrtc';
import { Input, Field, SemanticError } from 'DeskPRO/Component/Semantic/ReactForm';
import { UploadButton } from 'DeskPRO/Component/Uploader/UploadButton';
import BaseUploadTab from './BaseUploadTab';
import { UploadPlayButton } from './PlayButton';

class RecordTab extends BaseUploadTab {

  constructor(props) {
    super(props);
    this.state.recording = false;
  }

  onToggleRecording = (event) => {
    event.preventDefault();
    if (this.locked) {
      return;
    }

    this.locked = true;

    if (!this.state.recording) {
      navigator.mediaDevices.getUserMedia({ audio: true })
        .then((stream) => {
          this.stream = stream;
          this.recordRTC = RecordRTC(this.stream, {
            type:         'audio',
            mimeType:     'audio/wav',
            recorderType: RecordRTC.StereoAudioRecorder,
            bufferSize:   0,
            sampleRate:   44100,
            leftChannel:  false,
            disableLogs:  false
          });

          this.recordRTC.startRecording();
          this.setState({
            blobAuth:    null,
            downloadUrl: null,
            recording:   true,
            upload:      false,
            playing:     false,
            uploadError: null
          }, () => {
            this.locked = false;
          });
        })
        .catch(() => {});
    } else {
      this.recordRTC.stopRecording(() => {
        const blob = this.recordRTC.getBlob();
        const file = new File([blob], 'record.wav', { type: 'audio/wav' });

        this.stream.stop();
        this.setState({
          recording:   false,
          blobAuth:    null,
          downloadUrl: null,
          upload:      true,
          playing:     false,
          uploadError: null
        }, () => {
          this.locked = false;
          this.upload.pushFileToQueue(file);
        });
      });
    }
  };

  render() {
    const { downloadUrl, upload, uploadError, recording } = this.state;
    const baseUrl = window.DP_BASE_URL ? window.DP_BASE_URL.replace(/\/$/, '') : '';

    return (
      <div className="record-tab">
        <Field select="name" label="Name your audio">
          <Input type="text" />
        </Field>
        <button
          className={classNames('ui basic button', { loading: upload, disabled: upload })}
          onClick={this.onToggleRecording}
        >
          <i className={classNames(recording ? 'mute' : 'unmute', 'icon')} />
          Start recording
        </button>
        <UploadPlayButton
          ref={(c) => { this.playButton = c; }}
          downloadUrl={downloadUrl}
          label="Play back"
        />
        <UploadButton
          ref={(c) => { this.upload = c; }}
          name="file"
          uploadUrl={`${baseUrl}/api/v2/blobs/temp`}
          onSuccess={this.onUploadSuccess}
          onFail={this.onFail}
        />
        {uploadError &&
          <div>
            <SemanticError error={{ message: uploadError }} />
          </div>}
      </div>
    );
  }
}

export default RecordTab;
