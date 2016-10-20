import BaseAudioWidgetTab from './BaseAudioWidgetTab';

class BaseUploadTab extends BaseAudioWidgetTab {

  constructor(props) {
    super(props);
    const blob = props.value.blob;

    this.state = {
      blobAuth:    blob ? blob.blob_auth : null,
      downloadUrl: blob ? blob.download_url : null,
      upload:      false,
      playing:     false,
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

  onToggleSound = (event) => {
    event.preventDefault();
    const { downloadUrl, playing } = this.state;

    if (!downloadUrl) {
      return;
    }

    if (!playing) {
      this.audio.src = downloadUrl;
      this.audio.play();
      this.setState({
        playing: true
      });
    } else {
      this.audio.pause();
      this.audio.currentTime = 0;
      this.setState({
        playing: false
      });
    }
  };

}

export default BaseUploadTab;
