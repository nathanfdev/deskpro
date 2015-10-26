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
      <div>
        {this.state.tmpFilePath && (<Cropper
          ref="cropper"
          src={this.state.tmpFilePath}
          style={{height: 400, width: '100%'}}
          // Cropper.js options
          aspectRatio={16 / 9}
          guides={false}
          crop={this._crop} />)}

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
