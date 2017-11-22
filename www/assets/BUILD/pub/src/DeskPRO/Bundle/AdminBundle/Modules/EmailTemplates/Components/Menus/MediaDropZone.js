import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { UploadButton } from 'DeskPRO/Component/Uploader/UploadButton';
import DropZone from 'DeskPRO/Component/Uploader/DropZone';

class MediaDropZone extends React.Component {
  static propTypes = {
    icon:      PropTypes.string,
    type:      PropTypes.string,
    onFail:    PropTypes.func,
    onSend:    PropTypes.func,
    onSuccess: PropTypes.func
  };
  static defaultProps = {
    onFail() {},
    onSend() {},
    onSuccess() {}
  };

  onFail = () => {
    // TODO show error
    this.props.onFail();
  };

  onSend = () => {
    this.props.onSend();
  };

  getUploadUrl = () => `/api/v2/email_templates/email_assets/${this.props.type}`;

  handleSuccess = () => {
    this.props.onSuccess(this.props.type);
  };

  render() {
    const { icon, type } = this.props;
    return (<DropZone
      getExternalInput={() => this.uploadButton.input}
      uploadUrl={this.getUploadUrl()}
      onSend={this.onSend}
      onSuccess={this.handleSuccess}
      onFail={this.onFail}
      ref={(c) => { this.node = c; }}
    >
      <div className="drop-zone">
        <i className={classNames('icon', icon)} />
        Drop new files here or
        <UploadButton
          id={`upload_${type}`}
          ref={(c) => { this.uploadButton = c; }}
          className="hidden"
          name="file"
          onSend={this.onSend}
          onSuccess={this.handleSuccess}
          onFail={this.onFail}
          uploadUrl={this.getUploadUrl()}
        />
        <label className="ui button small basic" htmlFor={`upload_${type}`}>Upload files</label>
      </div>
    </DropZone>);
  }
}
export default MediaDropZone;
