import React from 'react';
import Cropper from 'react-cropper';
import DropzoneComponent from 'react-dropzone-component';

export class Avatar extends React.Component {

  render() {
    const djsConfig = {
      addRemoveLinks: true,
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
        <Cropper
          ref="cropper"
          src="http://fengyuanchen.github.io/cropper/img/picture.jpg"
          style={{height: 400, width: '100%'}}
          // Cropper.js options
          aspectRatio={16 / 9}
          guides={false}
          crop={this._crop} />

        <DropzoneComponent config={componentConfig}
                           eventHandlers={[]}
                           djsConfig={djsConfig} />
        </div>
    );
  }
}
