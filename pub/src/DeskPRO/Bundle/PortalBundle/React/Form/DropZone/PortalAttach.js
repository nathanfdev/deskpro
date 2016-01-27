import React from 'react';
import { portalUrlGenerator } from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';
import { AttachedList } from './AttachedList';

const qq = require('exports?qq!fine-uploader/fine-uploader/fine-uploader.js');

export class PortalAttach extends React.Component {

  constructor(props) {
    super(props);
    this.uploader = null;
    this.state = {
      files: []
    };
  }

  componentDidMount() {
    const params = {};
    if (window.dp_get_csrf_token) {
      params['file[_dp_csrf_token]'] = window.dp_get_csrf_token();
    }

    const uploaderOpts = {
      multiple: true,
      button: this.refs.btn,
      dropZoneElements: this.refs.btn,
      debug: true,
      request: {
        endpoint: portalUrlGenerator.path('/') + 'dpblob',
        method: 'POST',
        inputName: 'file[blob]',
        params: params
      },
      callbacks: {
        onUpload: this.onUpload,
        onComplete: this.onComplete
      }
    };

    this.uploader = new qq.FineUploaderBasic(uploaderOpts);
  }

  componentWillUnmount() {
    if (this.uploader) {
      this.uploader.cancelAll();
      this.uploader.reset();
      this.uploader = null;
    }
  }

  onUpload = (id, name) => {
    const f = {
      id: id,
      filename: name,
      blob: null,
      status: 'uploading'
    };

    const files = this.state.files.slice();
    files.push(f);

    this.setState({
      files: files,
      lastError: null
    });
  };

  onComplete = (id, name, res) => {
    if (res && res.success && res.success === true) {
      const blob = res.blob;
      const newFiles = [];

      this.state.files.forEach(f => {
        if (f.id !== id) {
          newFiles.push(f);
        } else {
          newFiles.push({
            ...f,
            status: 'done',
            blob: blob
          });
        }
      });

      this.setState({
        files: newFiles
      });
    } else {
      this.setState({
        files: this.state.files.filter(f => f.id !== id),
        lastError: res.error || { message: 'Could not upload file', code: 0, detail: null }
      });
    }
  };

  onDelete = file => {
    const newFiles = [];
    this.state.files.forEach(f => {
      if (f.id !== file.id) {
        newFiles.push(f);
      }
    });

    this.setState({
      files: newFiles
    });
  };

  render() {
    return (
       <div className="new-ticket-attachements" ref="main">
          <span className="attach-file" ref="btn">
            <i className="fa fa-upload" />
            <span className="text">Drag a file in here or</span>
            <span className="fake-button">Choose a file</span>
          </span>

         <AttachedList files={this.state.files} onDelete={this.onDelete} />
         {this.state.lastError && this.state.lastError.message}
      </div>
    );
  }
}
