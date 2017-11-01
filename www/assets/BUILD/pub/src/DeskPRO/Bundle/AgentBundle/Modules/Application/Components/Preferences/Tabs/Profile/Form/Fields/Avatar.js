import PropTypes from 'prop-types';
import React from 'react';
import { Cropper } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Cropper';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Avatar as AvatarIcon } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/Avatar';
import Dropzone from 'dropzone';
import DropzoneComponent from 'react-dropzone-component';
import jQuery from 'jquery';

export class Avatar extends React.Component {

  static propTypes = {
    personName: PropTypes.string,
    value:      PropTypes.string,
    onChange:   PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      tmpFile:     null,
      tmpFilePath: null,
      croppedPath: null,
      edit:        false,
      error:       null
    };
  }

  onCropThumbnail = (file) => {
    if (file.cropped) {
      return;
    }

    const reader = new FileReader();
    reader.onloadend = () => {
      this.setState({
        tmpFile:     file,
        tmpFilePath: reader.result,
        error:       null
      });
    };

    reader.readAsDataURL(file);
    this.refs.dropzoneComponent.dropzone.removeFile(file);
  };

  onDiscard = () => {
    this.setState({
      tmpFile:     null,
      tmpFilePath: null,
      croppedPath: null,
      edit:        false,
      error:       null
    });
  };

  onSave = () => {
    const dropzone = this.refs.dropzoneComponent.dropzone;
    if (dropzone.getUploadingFiles().length) {
      return;
    }

    const cropper = this.refs.cropper;
    if (!cropper) {
      return;
    }

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
      edit:    false,
      error:   null
    });

    this.props.onChange(response.data.blob_auth_id);
    this.refs.dropzoneComponent.dropzone.removeFile(file);
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
    if (this.state.tmpFile) {
      return;
    }

    this.setState({
      edit: !this.state.edit
    });
  };

  onCloseEdit = () => {
    if (this.state.tmpFile) {
      return;
    }

    this.setState({
      edit: false
    });
  };

  onRemove = () => {
    this.onDiscard();
    this.props.onChange(null);
  };

  getImagePath() {
    const currentPath = this.props.value;
    const croppedPath = this.state.croppedPath;

    return croppedPath || currentPath;
  }

  getPersonInitials() {
    const { personName } = this.props;
    if (personName) {
      const parts = personName.split(' ');

      let lastNameChar = '';
      if (parts.length > 1) {
        lastNameChar = parts.pop()[0];
      }

      const firstNameChar = parts.shift()[0];

      return firstNameChar + lastNameChar;
    }

    return '?';
  }

  renderRemoveButton() {
    if (this.getImagePath()) {
      return (
        <div>
          <a href="#" className="cancel" onClick={this.onRemove}>Or remove &amp; your avatar (use the default)</a>
        </div>
      );
    }
  }

  renderCropperButtons() {
    return (
      <div>
        <a href="#" className="crop" onClick={this.onSave}>Crop &amp; Save Avatar</a>
        <a href="#" className="cancel" onClick={this.onDiscard}>Or cancel &amp; discard your changes</a>
      </div>
    );
  }

  renderUploaderPopup() {
    const tmpFile = this.state.tmpFile;
    const tmpPath = this.state.tmpFilePath;
    const error = this.state.error;
    const djsConfig = {
      autoQueue:         false,
      maxFiles:          1,
      previewsContainer: false
    };

    const componentConfig = {
      postUrl: `${DP_BASE_URL}/api/v2/blobs/temp`
    };

    const classNames = ['avatar-crop'];
    if (!this.state.edit) {
      classNames.push('hidden');
    }

    return (
      <div className={classNames.join(' ')} id="avatar-crop">
        {!tmpFile && (
          <div className="controls">
            <a href="#" onClick={this.onToggleEdit}><i className="fa fa-times" /></a>
          </div>
        )}

        {tmpFile && (
          <p>Click &amp; drag to crop your avatar</p>
        )}
        <div className="cropper-bucket">
          {(this.getImagePath() && !tmpFile) && ((<img src={this.getImagePath()} />))}

          <DropzoneComponent
            className={tmpFile && 'hidden'}
            ref="dropzoneComponent"
            config={componentConfig}
            eventHandlers={{
              thumbnail: this.onCropThumbnail,
              success:   this.onSuccess,
              error:     this.onError
            }}
            djsConfig={djsConfig}
          >
            <div className="dz-message">
              {this.getImagePath()
                ? (<a href="#" className="crop" onClick={this.onSave}>Upload &amp; a new avatar</a>)
                : (<span>Drag and drop a photo here or click to browse.</span>)}
            </div>
          </DropzoneComponent>

          {tmpFile && (
            <Cropper
              ref="cropper"
              src={tmpPath}
              minCropBoxWidth={120}
              minCropBoxHeight={120}
              aspectRatio={1 / 1}
            />
          )}
        </div>

        {error && (<span className="error">{error}</span>)}
        {tmpFile ? this.renderCropperButtons() : this.renderRemoveButton()}
      </div>
    );
  }

  render() {
    return (
      <div className="bucket-column-last">
        <a href="#" className="button button-secondary user-avatar" onClick={this.onToggleEdit} ref="editButton">
          <AvatarIcon
            size={24}
            color="#CDD2D4"
            urlPattern={this.getImagePath()}
            text={this.getPersonInitials()}
          />
          Manage Avatar
        </a>
        <ClickOut
          onClickOut={this.onCloseEdit}
          additionalNodes={[this.refs.editButton]}
          ignoreNodes={[jQuery('.dz-hidden-input')[0]]}
        >

          {this.renderUploaderPopup()}
        </ClickOut>
      </div>
    );
  }
}
