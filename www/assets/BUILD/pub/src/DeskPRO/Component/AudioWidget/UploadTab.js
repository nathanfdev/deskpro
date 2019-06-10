import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { SemanticError } from 'DeskPRO/Component/Semantic/ReactForm';
import { UploadButton } from 'DeskPRO/Component/Uploader/UploadButton';
import BaseUploadTab from './BaseUploadTab';
import { UploadPlayButton } from './PlayButton';

class UploadTab extends BaseUploadTab {

  static propTypes = {
    value: PropTypes.object
  };

  onUpload = () => {
    this.playButton.stopPlaying();
    this.setState({
      blobAuth:    null,
      downloadUrl: null,
      upload:      true,
      uploadError: null
    });
  };

  render() {
    const { value } = this.props;
    const { downloadUrl, upload, uploadError } = this.state;
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
          {value && value.blob && value.blob.blob_auth ? 'Choose a different file' : 'Choose a file'}
          <UploadButton
            name="file"
            uploadUrl={`${baseUrl}/api/v2/blobs/temp`}
            acceptFileTypes={/(\.|\/)(mp3)$/i}
            onSubmit={this.onUpload}
            onSuccess={this.onUploadSuccess}
            onFail={this.onFail}
          />
        </button>
        <UploadPlayButton
          ref={(c) => { this.playButton = c; }}
          downloadUrl={downloadUrl}
          iconOnly
        />
        {uploadError &&
          <div>
            <SemanticError error={{ message: uploadError }} />
          </div>}
      </div>
    );
  }
}

export default UploadTab;
