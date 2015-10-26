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

  onThumbnail = file => {
    if (file.cropped) {
      return;
    }

    const dropzone = this.refs.dropzoneComponent.dropzone;
    const reader = new FileReader();
    reader.onloadend = () => {
      this.setState({
        tmpFilePath: reader.result
      });
    };

    dropzone.removeFile(file);
    reader.readAsDataURL(file);

    this.setState({
      tmpFile: file
    });
  };

  onDiscard = () => {
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
            style={{height: 400, width: '100%'}}
            // Cropper.js options
            aspectRatio={16 / 9}
            guides={false}
            crop={this._crop} />
        </div>
        <a href="#" className="crop">Crop &amp; Save Avatar</a>
        <a href="#" className="cancel" onClick={this.onDiscard}>Or cancel &amp; discard your changes</a>
      </div>
    );
  }

  render() {
    const djsConfig = {
      addRemoveLinks: true,
      maxFiles: 1,
      params: {
        myParameter: "I'm a parameter!"
      }
    };

    const componentConfig = {
      iconFiletypes: ['.jpg', '.png', '.gif'],
      showFiletypeIcon: true,
      postUrl: '/uploadHandler'
    };

    return (
      <div className="bucket-column-last">
        {this.state.tmpFilePath ? this.renderCropper() : null}

        <DropzoneComponent ref="dropzoneComponent"
                           config={componentConfig}
                           eventHandlers={{
                             thumbnail: this.onThumbnail
                           }}
                           djsConfig={djsConfig} />
      </div>
    );
  }
}
