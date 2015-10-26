import React from 'react';
import ReactDOM from 'react-dom';
import Cropper from 'react-cropper';

export class Avatar extends React.Component {

  render() {
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
        </div>
    );
  }
}

export class FileUpload extends React.Component {

  handleFile = (e) => {
    var reader = new FileReader();
    var file = e.target.files[0];

    if (!file) return;

    reader.onload = function(img) {
      ReactDOM.findDOMNode(this.refs.in).value = '';
      this.props.handleFileChange(img.target.result);
    }.bind(this);
    reader.readAsDataURL(file);
  };

  render() {
    return (
      <input ref="in" type="file" accept="image/*" onChange={this.handleFile} />
    );
  }
}
