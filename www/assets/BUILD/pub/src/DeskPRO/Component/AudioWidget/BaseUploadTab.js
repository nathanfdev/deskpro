import PropTypes from 'prop-types';
import React from 'react';

class BaseUploadTab extends React.Component {

  static propTypes = {
    value:    PropTypes.object,
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);
    const blob = props.value.blob;

    this.state = {
      blobAuth:    blob ? blob.blob_auth : null,
      downloadUrl: blob ? blob.download_url : null,
      upload:      false,
      uploadError: null
    };
  }

  onUploadSuccess = (event, data) => {
    const { onChange } = this.props;
    const info = data.result && data.result.data ? data.result.data : {};
    const blobAuth = info.blob_auth_id;
    const downloadUrl = info.download_url;

    if (blobAuth) {
      this.setState({
        upload: false,
        blobAuth,
        downloadUrl
      });

      onChange({
        blob: {
          blob_auth: blobAuth
        }
      });
    } else {
      this.setState({
        blobAuth:    null,
        downloadUrl: null,
        upload:      false,
        uploadError: 'Unable to upload file'
      });
    }
  };

  onFail = () => {
    this.setState({
      blobAuth:    null,
      downloadUrl: null,
      upload:      false,
      uploadError: 'Unable to upload file'
    });
  };

  stopPlaying() {
    this.playButton.stopPlaying();
  }
}

export default BaseUploadTab;
