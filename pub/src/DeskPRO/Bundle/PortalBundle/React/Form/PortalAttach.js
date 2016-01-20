import React, { PropTypes } from 'react';
import PortalPhrases from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import PortalUrlGenerator from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';

const qq = require('exports?qq!fine-uploader/fine-uploader/fine-uploader.js');

export default class PortalAttach extends React.Component {

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
        endpoint: PortalUrlGenerator.path('/') + 'dpblob',
        method: 'POST',
        inputName: 'file[blob]',
        params: params
      },
      callbacks: {
        onUpload: this.beginUpload,
        onComplete: this.doneUpload
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

  beginUpload = (id, name) => {
    const f = {
      id: id,
      filename: name,
      blob: null,
      status: 'uploading'
    };

    const files = this.state.files.slice();
    files.push(f);

    this.setState({ files: files, lastError: null });
  };

  doneUpload = (id, name, res) => {
    if (res && res.success && res.success === true) {
      const blob = res.blob;
      const newFiles = [];

      this.state.files.forEach(f => {
        if (f.id !== id) {
          newFiles.push(f);
        } else {
          const newF = Object.assign({}, f, {
            status: 'done',
            blob: blob
          });
          newFiles.push(newF);
        }
      });

      this.setState({ files: newFiles });
    } else {
      this.setState({
        files: this.state.files.filter(f => f.id !== id),
        lastError: res.error || { message: 'Could not upload file', code: 0, detail: null }
      });
    }
  };

  handleDelete = (file) => {
    const newFiles = [];

    this.state.files.forEach(f => {
      if (f.id !== file.id) {
        newFiles.push(f);
      }
    });

    this.setState({ files: newFiles });
  };

  render() {
    return (
       <div className="new-ticket-attachements" ref="main">
          <span className="attach-file" ref="btn">
            <i className="fa fa-upload" />
            <span className="text">Drag a file in here or</span>
            <span className="fake-button">Choose a file</span>
          </span>
         <PortalAttachList files={this.state.files} handleDelete={this.handleDelete} />
      </div>
    );
  }
}


class PortalAttachList extends React.Component {

  static propTypes = {
    handleDelete: PropTypes.func,
    files: PropTypes.array.isRequired
  };

  handleDelete = (ev, file) => {
    ev.preventDefault();
    if (this.props.handleDelete) {
      this.props.handleDelete(file);
    }
  };

  render() {
    const files = this.props.files;
    if (!files.length) {
      return null;
    }

    return (
      <ul>
        {files.map(f => f.status === 'done'
          ? <PortalAttachListItem file={f} key={f.id} onDelete={this.handleDelete} />
          : <PortalAttachListItemUploading file={f} key={f.id} />
        )}
      </ul>
    );
  }
}

class PortalAttachListItemUploading extends React.Component {

  static propTypes = {
    file: PropTypes.object.isRequired
  };

  render() {
    const f = this.props.file;
    return (
      <li>
        <a href={f.url}>
          {f.filename}
        </a>
      </li>
    );
  }
}

class PortalAttachListItem extends React.Component {

  static propTypes = {
    file: PropTypes.object.isRequired,
    onDelete: PropTypes.func
  };

  onDelete = (ev) => {
    ev.preventDefault();
    if (this.props.onDelete) {
      this.props.onDelete(ev, this.props.file);
    }
  };

  viewFile = (ev) => {
    ev.preventDefault();
    window.open(this.props.file.blob.url);
  };

  render() {
    const f = this.props.file;
    const blob = f.blob;
    const formName = `ticket[attachments][${blob.id}][blob_auth]`;
    return (
      <li>
        <span dangerouslySetInnerHTML={{__html: blob.icon_html }} />
        <a href={blob.url} target="_blank" onClick={this.viewFile}>{f.filename}</a>
        <input type="hidden" name={formName} value={blob.authcode} />
        <span className="file-size">({blob.size})</span>
        <a href="#" className="remove-attachement" onClick={this.onDelete}>
          <i className="fa fa-times" />{PortalPhrases.get('portal.general.delete')}
        </a>
      </li>
    );
  }
}
