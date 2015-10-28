import React, { PropTypes } from 'react';
import { Cropper } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Cropper';
import Dropzone from 'dropzone';
import DropzoneComponent from 'react-dropzone-component';

export class Avatar extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      tmpFile: null,
      tmpFilePath: null,
      croppedPath: null,
      edit: false,
      error: null
    };
  }

  onCropThumbnail = file => {
    if (file.cropped) {
      return;
    }

    const reader = new FileReader();
    reader.onloadend = () => {
      this.setState({
        tmpFile: file,
        tmpFilePath: reader.result,
        error: null
      });
    };

    reader.readAsDataURL(file);
    this.refs.dropzoneComponent.dropzone.removeFile(file);
  };

  onDiscard = () => {
    this.setState({
      tmpFile: null,
      tmpFilePath: null,
      croppedPath: null,
      edit: false,
      error: null
    });
  };

  onSave = () => {
    const dropzone = this.refs.dropzoneComponent.dropzone;

    if (dropzone.getUploadingFiles().length) {
      return;
    }

    const cropper = this.refs.cropper;
    const blobUrl = cropper.getCroppedCanvas().toDataURL();
    const byteString = atob(blobUrl.split(',')[1]);
    const ab = new ArrayBuffer(byteString.length);
    const ia = new Uint8Array(ab);

    for (let num = 0; num < byteString.length; num++) {
      ia[num] = byteString.charCodeAt(num);
    }

    const file = this.state.tmpFile;
    const blob = new Blob([ab]);
    const croppedFile = new File([blob], file.name, {
      cropped: true
    });

    this.setState({
      croppedPath: blobUrl
    });

    dropzone.addFile(croppedFile);
    dropzone.enqueueFile(croppedFile);
    dropzone.processQueue();
  };

  onSuccess = (file, response) => {
    this.setState({
      tmpFile: null,
      tmpPath: null,
      edit: false,
      error: null
    });

    this.props.onChange(response.blob_auth_id);
  };

  onError = (file, response) => {
    if (file.status !== Dropzone.ERROR) {
      return;
    }

    file.status = Dropzone.ADDED;
    file.accepted = true;

    let message;
    if (response && response.error) {
      message = response.error.message;
    } else {
      message = 'An unknown error has occurred while uploading, please re-try again.';
    }

    this.setState({
      error: message
    });
  };

  onToggleEdit = () => {
    this.setState({
      edit: !this.state.edit
    });
  };

  renderUploader() {
    const tmpPath = this.state.tmpFilePath;
    const tmpFile = this.state.tmpFile;
    const croppedPath = this.state.croppedPath;
    const error = this.state.error;
    const djsConfig = {
      autoQueue: false,
      maxFiles: 1,
      previewsContainer: false
    };

    const componentConfig = {
      postUrl: `${DP_BASE_URL}/api/v2/blobs/temp`
    };

    return (
      <div className="avatar-crop" id="avatar-crop">
        {tmpFile && (
          <p>Click &amp; drag to crop your avatar</p>
        )}
        <div className="cropper-bucket">
          <DropzoneComponent className={tmpFile && 'hidden'}
                             ref="dropzoneComponent"
                             config={componentConfig}
                             eventHandlers={{
                               thumbnail: this.onCropThumbnail,
                               success: this.onSuccess,
                               error: this.onError
                             }}
                             djsConfig={djsConfig}>
            <div className="dz-message">
              {this.props.value
                ? (<img src={croppedPath || this.props.value} />)
                : (<span>Drag and drop a photo here or click to browse.</span>)}
            </div>
          </DropzoneComponent>

          {tmpFile && (
            <Cropper
              ref="cropper"
              src={tmpPath}
              aspectRatio={1 / 1} />
          )}
        </div>

        {error && (<span className="error">{error}</span>)}

        {tmpFile && (
          <div>
            <a href="#" className="crop" onClick={this.onSave}>Crop &amp; Save Avatar</a>
            <a href="#" className="cancel" onClick={this.onDiscard}>Or cancel &amp; discard your changes</a>
          </div>
        )}
      </div>
    );
  }

  render() {
    return (
      <div className="bucket-column-last">
        <a href="#" className="button button-secondary user-avatar" onClick={this.onToggleEdit}>
          <span className="icon"></span>
          Manage Avatar
        </a>
        {this.state.edit && this.renderUploader()}
      </div>
    );
  }
}
