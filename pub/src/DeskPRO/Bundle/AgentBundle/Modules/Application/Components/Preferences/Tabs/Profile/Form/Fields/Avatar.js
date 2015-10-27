import React from 'react';
import Cropper from 'react-cropper';
import DropzoneComponent from 'react-dropzone-component';

export class Avatar extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      tmpFile: null,
      tmpFilePath: null
    };
  }

  onCropThumbnail = (file, dataUrl) => {
    if (file.cropped) {
      return;
    }

    this.setState({
      tmpFile: file,
      tmpFilePath: dataUrl
    });
  };

  onDiscard = () => {
    this.setState({
      tmpFile: null,
      tmpFilePath: null
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

    this.setState({
      tmpFile: null,
      tmpFilePath: null
    });
  };

  renderCropper() {
    return (
      <div className="avatar-crop" id="avatar-crop">
        <p>Click &amp; drag to crop your avatar</p>
        <div className="cropper-bucket">
          <Cropper
            ref="cropper"
            src={this.state.tmpFilePath}
            style={{height: 200, width: '100%'}}
            // Cropper.js options
            aspectRatio={16 / 9}
            guides={false} />
        </div>
        <a href="#" className="crop" onClick={this.onSave}>Crop &amp; Save Avatar</a>
        <a href="#" className="cancel" onClick={this.onDiscard}>Or cancel &amp; discard your changes</a>
      </div>
    );
  }

  render() {
    const djsConfig = {
      addRemoveLinks: true,
      autoQueue: false,
      maxFiles: 1
    };

    const componentConfig = {
      iconFiletypes: ['.jpg', '.png', '.gif'],
      showFiletypeIcon: true,
      postUrl: `${DP_BASE_URL}/api/v2/blobs/temp`
    };

    return (
      <div className="bucket-column-last">
        {this.state.tmpFilePath ? this.renderCropper() : null}

        <DropzoneComponent ref="dropzoneComponent"
                           config={componentConfig}
                           eventHandlers={{
                             thumbnail: this.onCropThumbnail
                           }}
                           djsConfig={djsConfig} />
      </div>
    );
  }
}
