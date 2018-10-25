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

  constructor(props) {
    super(props);
    this.state = {
      error: null
    };
  }

  onFail = (event, data) => {
    if (data.errorThrown === 'Request Entity Too Large') {
      this.setState({
        error: 'File size over the limit'
      });
    } else if (data.errorThrown === 'Bad Request') {
      this.setState({
        error: data._response.jqXHR.responseJSON.message // eslint-disable-line no-underscore-dangle
      });
    }
    this.props.onFail();
  };

  onSend = () => {
    this.setState({
      error: null
    });
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
      { this.state.error ?
        <div className="error">
          {this.state.error}
        </div> : null
      }
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
