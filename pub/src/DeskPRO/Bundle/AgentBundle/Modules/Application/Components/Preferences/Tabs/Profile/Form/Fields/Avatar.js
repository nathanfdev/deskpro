import React, { PropTypes } from 'react';
import Cropper from 'react-cropper';
import DropzoneComponent from 'react-dropzone-component';

export class Avatar extends React.Component {

  static propTypes = {
    value: PropTypes.object,
    onChange: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      tmpFile: null,
      tmpFilePath: null,
      edit: false
    };
  }

  onCropThumbnail = (file, dataUrl) => {
    if (file.cropped) {
      return;
    }

    this.refs.dropzoneComponent.dropzone.removeFile(file);
    this.setState({
      tmpFile: file,
      tmpFilePath: dataUrl
    });
  };

  onDiscard = () => {
    this.setState({
      tmpFile: null,
      tmpFilePath: null,
      edit: false
    });
  };

  onSave = () => {
    const cropper = this.refs.cropper;
    const blobUrl = cropper.getCroppedCanvas().toDataURL();
    const byteString = atob(blobUrl.split(',')[1]);
    const ab = new ArrayBuffer(byteString.length);
    const ia = new Uint8Array(ab);

    for (let num = 0; num < byteString.length; num++) {
      ia[num] = byteString.charCodeAt(num);
    }

    const file = this.state.tmpFile;
    const dropzone = this.refs.dropzoneComponent.dropzone;
    const blob = new Blob([ab]);
    const croppedFile = new File([blob], file.name, {
      cropped: true
    });

    dropzone.addFile(croppedFile);
    dropzone.enqueueFile(croppedFile);
    dropzone.processQueue();
  };

  onComplete = file => {
    console.log('complete', file.xhr.response);
    this.setState({
      tmpFile: null,
      tmpFilePath: null,
      edit: false
    });
  };

  onEdit = () => {
    this.setState({
      edit: true
    });
  };

  renderUploader() {
    const tmpPath = this.state.tmpFilePath;
    const djsConfig = {
      addRemoveLinks: true,
      autoQueue: false,
      maxFiles: 1,
      previewsContainer: false
    };

    const componentConfig = {
      postUrl: `${DP_BASE_URL}/api/v2/blobs/temp`
    };

    return (
      <div className="avatar-crop" id="avatar-crop">
        {tmpPath && (
          <p>Click &amp; drag to crop your avatar</p>
        )}
        <div className="cropper-bucket">
          <DropzoneComponent className={tmpPath && 'hidden'}
                             ref="dropzoneComponent"
                             config={componentConfig}
                             eventHandlers={{
                               thumbnail: this.onCropThumbnail,
                               complete: this.onComplete
                             }}
                             djsConfig={djsConfig}>
            <div className="dz-message">
              {this.props.value
                ? (<img src={this.props.value} />)
                : (<span>Drag and drop a photo here or click to browse.</span>)}
            </div>
          </DropzoneComponent>

          {tmpPath && (
            <Cropper
            ref="cropper"
            src={tmpPath}
            style={{height: 200, width: '100%'}}
            aspectRatio={16 / 9}
            guides={false} />
          )}
        </div>

        {tmpPath && (
          <a href="#" className="crop" onClick={this.onSave}>Crop &amp; Save Avatar</a>
        )}
        <a href="#" className="cancel" onClick={this.onDiscard}>Or cancel &amp; discard your changes</a>
      </div>
    );
  }

  render() {
    return (
      <div className="bucket-column-last">
        <a href="#" className="button button-secondary user-avatar" onClick={this.onEdit}>
          <span className="icon"></span>
          Manage Avatar
        </a>
        {this.state.edit && this.renderUploader()}
      </div>
    );
  }
}
