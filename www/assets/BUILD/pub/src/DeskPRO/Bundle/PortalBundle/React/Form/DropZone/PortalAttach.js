import React, { PropTypes } from 'react';
import { portalUrlGenerator } from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';
import { DropZone } from 'DeskPRO/Component/Uploader/DropZone';
import { AttachedList } from './AttachedList';
import { pageWidgetEmitter } from 'DeskPRO/Component/PageWidget/PageWidgetEmitter';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class PortalAttach extends React.Component {

  static propTypes = {
    widgetOptions: PropTypes.object,
    $input:        PropTypes.object,
    inputName:     PropTypes.string
  };

  constructor(props) {
    super(props);
    this.uploader = null;
    this.state = {
      files: []
    };
  }

  componentDidMount() {
    const { $input } = this.props;

    pageWidgetEmitter.on('rteFileUpload', this.onRteFileUpload);

    $input.on('setBlobs', (event, blobs) => {
      this.setState({
        files: blobs
      });
    });
    $input.closest('form').on('reset', () => {
      this.setState({
        files: []
      });
    });
  }

  onRteFileUpload = (file) => {
    this.refs.dropZone.pushFileToQueue(file);
  };

  onUploadStarted = (event, data) => {
    const files = this.state.files.slice();
    files.push({
      file: data.files[0],
      info: null
    });

    this.setState({
      files,
      lastError: null
    });
  };

  onUploadSuccess = (event, data) => {
    const { $input } = this.props;
    const file = data.files[0];
    const info = data.result && data.result.blob || {};
    const newFiles = [];

    this.state.files.forEach(f => {
      if (f.file !== file) {
        newFiles.push(f);
      } else {
        newFiles.push({ ...f, info });
      }
    });

    $input.trigger('blobs', [newFiles]);
    this.setState({
      files: newFiles
    });
  };

  onUploadFail = (event, data) => {
    const file = data.files[0];
    const response = data.jqXHR.responseJSON;
    const error = response && response.error;

    this.setState({
      files:     this.state.files.filter(f => f.file !== file),
      lastError: error && error.message || portalPhrases.get('portal.forms.error_upload_file')
    });
  };

  onDelete = file => {
    const { $input } = this.props;
    const newFiles = this.state.files.filter(f => f.info !== file.info);

    $input.trigger('blobs', newFiles);
    this.setState({
      files: newFiles
    });
  };

  render() {
    const { widgetOptions, inputName } = this.props;
    const context = widgetOptions.context || document;

    const params = {};
    if (window.dp_get_csrf_token) {
      params['file[_dp_csrf_token]'] = window.dp_get_csrf_token();
    }

    return (
      <div className="new-ticket-attachments">
        <DropZone
          ref="dropZone"
          getExternalInput={() => this.refs.fileUpload}
          uploadUrl={`${portalUrlGenerator.path('/')}dpblob`}
          uploadParams={params}
          context={context}
          onSend={this.onUploadStarted}
          onSuccess={this.onUploadSuccess}
          onFail={this.onUploadFail}
        >
          <span className="attach-file">
            <i className="fa fa-upload" />
            <span className="text">{portalPhrases.get('portal.forms.label_drag')}</span>
            <span className="fake-button">{portalPhrases.get('portal.forms.label_choose')}</span>
            <input type="file" ref="fileUpload" name="file[blob]" />
          </span>
        </DropZone>

        <AttachedList files={this.state.files} inputName={inputName} onDelete={this.onDelete} />
        {this.state.lastError}
      </div>
    );
  }
}
