import PropTypes from 'prop-types';
import React from 'react';
import $ from 'jquery';
import classNames from 'classnames';
import { pageWidgetEmitter } from 'DeskPRO/Component/PageWidget/PageWidgetEmitter';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { portalUrlGenerator } from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';
import DropZone from 'DeskPRO/Component/Uploader/DropZone';
import { Progress } from '@deskpro/portal-components';
import { AttachedList } from './AttachedList';

export default class HcAttach extends React.Component {

  static propTypes = {
    files:         PropTypes.array,
    widgetOptions: PropTypes.object,
    $input:        PropTypes.object,
    inputName:     PropTypes.string,
    maxFileSize:   PropTypes.string,
    $form:         PropTypes.object,
    multiple:      PropTypes.bool,
    customField:   PropTypes.bool,
    uploadUrl:     PropTypes.string
  };

  static defaultProps = {
    uploadUrl: 'dpblob',
    multiple:  true,
    progress:  0,
  };

  constructor(props) {
    super(props);
    this.uploader = null;
    this.state = {
      files: this.props.files || []
    };
  }

  componentDidMount() {
    const { $input, $form, customField } = this.props;

    if (!customField) {
      pageWidgetEmitter.on('rteFileUpload', this.onRteFileUpload);
    }

    $input.on('setBlobs', (event, blobs) => {
      if (!Array.isArray(blobs)) {
        return;
      }

      this.setState({
        files: blobs
      });
    });
    $form.on('reset', () => {
      this.setState({
        files: []
      });
    });

    $(document).bind('dragover', (e) => {
      const dropZones = $('.dp-pc_file-upload__dropzone');
      const timeout = window.dropZoneTimeout;
      if (timeout) {
        clearTimeout(timeout);
      } else {
        dropZones.addClass('in');
      }
      const hoveredDropZone = $(e.target).closest(dropZones);
      dropZones.not(hoveredDropZone).removeClass('active');
      hoveredDropZone.addClass('active');
      window.dropZoneTimeout = setTimeout(() => {
        window.dropZoneTimeout = null;
        dropZones.removeClass('in active');
      }, 100);
    });
  }

  onRteFileUpload = (file) => {
    this.refDropZone.pushFileToQueue(file);
  };

  onUploadStarted = (event, data) => {
    const { $form } = this.props;
    const $button = $form.find('button[type=submit]');
    $button.attr('disabled', 'disabled').addClass('disabled');
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
    const { $input, $form } = this.props;
    const file = data.files[0];
    const info = data.result ? data.result.blob : {};
    const newFiles = [];

    this.state.files.forEach((f) => {
      if (f.file !== file) {
        newFiles.push(f);
      } else {
        newFiles.push({ ...f, info });
      }
    });

    $input.trigger('blobs', [newFiles]);
    const $button = $form.find('button[type=submit]');
    $button.removeAttr('disabled').removeClass('disabled');
    this.setState({
      files:    newFiles,
      progress: 0,
    });
  };

  onUploadFail = (event, data) => {
    const { $form, maxFileSize } = this.props;
    const file = data.files[0];
    const response = data.jqXHR.responseJSON;
    const error = response && response.error;

    let lastError =  portalPhrases.get('portal.forms.error_upload_file');
    if (error && error.message) {
      lastError = error.message;
    }
    if (data.jqXHR.status === 413) {
      if (maxFileSize) {
        const maxFileInfo = maxFileSize.split(' ');

        lastError =  portalPhrases.get('portal.forms.error_upload_ini_size', {
          '{ limit }':  maxFileInfo[0],
          '{ suffix }': maxFileInfo[1]
        });
      } else {
        lastError = portalPhrases.get('portal.forms.error_upload_html_size');
      }
    }
    const $button = $form.find('button[type=submit]');
    $button.removeAttr('disabled').removeClass('disabled');
    this.setState({
      files:    this.state.files.filter(f => f.file !== file),
      lastError,
      progress: 0,
    });
  };

  onProgress = (e, data) => {
    const progress = parseInt(data.loaded / data.total * 100, 10);
    this.setState({
      progress,
    });
  };

  onDelete = (file) => {
    const { $input } = this.props;
    const newFiles = this.state.files.filter(f => f.info !== file.info);

    $input.trigger('blobs', [newFiles]);
    this.setState({
      files: newFiles
    });
  };

  handleBlur = () => {
    this.setState({
      focused: false
    });
  };

  handleFocus = () => {
    this.setState({
      focused: true
    });
  };

  handleMouseEnter = () => {
    this.setState({
      hovered: true
    });
  };

  handleMouseLeave = () => {
    this.setState({
      hovered: false
    });
  };

  renderLink() {
    // Somehow input node this.refFileUpload is not completely rerendered and holds first assigned id
    // if we already have an id - use it
    const rand = this.refFileUpload ? this.refFileUpload.id : Math.random();
    return (
      <span className="attach-file link">
        <input type="file" ref={(node) => { this.refFileUpload = node; }} id={rand} name="file[blob]" />
        <label htmlFor={rand}>
          <a>
            {portalPhrases.get('portal.widget.label_add_attachment')}
          </a>
        </label>
      </span>
    );
  }

  renderButton() {
    const { multiple } = this.props;

    return (
      <div
        className={classNames('dp-pc_file-upload__dropzone')}
        onMouseEnter={this.handleMouseEnter}
        onMouseLeave={this.handleMouseLeave}
      >
        <input
          type="file"
          ref={(node) => { this.refFileUpload = node; }}
          name="file[blob]"
          tabIndex="-1"
        />
        <div
          className="choose"
          tabIndex="0"
          onFocus={this.handleFocus}
          onBlur={this.handleBlur}
        >
          <i className="far fa-file-alt" />
          {multiple ? portalPhrases.get('helpcenter.forms.label_choose_files') : portalPhrases.get('helpcenter.forms.label_choose_a_file')}
        </div>
        <div className="or">{portalPhrases.get('helpcenter.general.or')}</div>
        <div className="dnd">
          <i className="far fa-clone" />
          {portalPhrases.get('helpcenter.forms.label_drag_and_drop')}
        </div>
        <Progress percent={this.state.progress} />
      </div>
    );
  }

  render() {
    const { widgetOptions, inputName, multiple, uploadUrl } = this.props;
    const { files, lastError } = this.state;
    const context = widgetOptions.context || document;

    const params = {};
    if (window.dp_get_csrf_token) {
      params['file[_dp_csrf_token]'] = window.dp_get_csrf_token();
    }

    return (
      <div className="dp-pc_field">
        <div
          className={
          classNames('dp-pc_file-upload', { focused: this.state.focused, hovered: this.state.hovered })
        }
        >
          {(multiple || !files.length) &&
            <DropZone
              ref={(node) => {
                this.refDropZone = node;
              }}
              getExternalInput={() => this.refFileUpload}
              uploadUrl={`${portalUrlGenerator.path('/')}${uploadUrl}`}
              uploadParams={params}
              context={context}
              onSend={this.onUploadStarted}
              onSuccess={this.onUploadSuccess}
              onProgress={this.onProgress}
              onFail={this.onUploadFail}
              getDropZoneNode={widgetOptions.getDropZoneNode}
            >
              {widgetOptions.isWidget ? this.renderLink() : this.renderButton()}
            </DropZone>
          }
          <AttachedList files={files} inputName={inputName} onDelete={this.onDelete} />
          <span className="dp-pc_file-upload__error">{lastError}</span>
        </div>
      </div>
    );
  }
}
