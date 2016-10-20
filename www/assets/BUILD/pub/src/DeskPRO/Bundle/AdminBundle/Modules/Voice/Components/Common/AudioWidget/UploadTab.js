import React from 'react';
import classNames from 'classnames';
import { SemanticError } from 'DeskPRO/Component/Semantic/ReactForm';
import { UploadButton } from 'DeskPRO/Component/Uploader/UploadButton';
import BaseUploadTab from './BaseUploadTab';

class UploadTab extends BaseUploadTab {

  onSubmit = () => {
    this.setState({
      blobAuth:    null,
      downloadUrl: null,
      upload:      true,
      playing:     false,
      uploadError: null
    });
  };

  render() {
    const { downloadUrl, upload, uploadError, playing } = this.state;
    const baseUrl = window.DP_BASE_URL ? window.DP_BASE_URL.replace(/\/$/, '') : '';

    return (
      <div className="upload-tab">
        <p className="format-note">
          Select the file you’d like to upload. Files must be .mp3 format.
        </p>
        <p className="rights-note">
          By uploading this file you acknowledge that you have the necessary rights to use it.
        </p>

        <button
          className={classNames('ui basic upload button', { loading: upload, disabled: upload })}
          onClick={() => false}
        >
          Choose a file
          <UploadButton
            name="file"
            uploadUrl={`${baseUrl}/api/v2/blobs/temp`}
            acceptFileTypes={/(\.|\/)(mp3)$/i}
            onSubmit={this.onSubmit}
            onSuccess={this.onUploadSuccess}
            onFail={this.onFail}
          />
        </button>
        <button
          className={classNames('ui basic icon button', { disabled: !downloadUrl })}
          onClick={this.onToggleSound}
        >
          <i className={classNames(playing ? 'stop' : 'play', 'icon')} />
        </button>
        <audio ref={(c) => { this.audio = c; }} />
        {uploadError &&
          <div>
            <SemanticError error={{ message: uploadError }} />
          </div>}
      </div>
    );
  }
}

export default UploadTab;
