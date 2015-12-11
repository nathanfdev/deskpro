import React, { PropTypes } from 'react';

export class AttachedFile extends React.Component {

  static propTypes = {
    attachment: PropTypes.object,
    onRemove: PropTypes.func
  };

  onRemove = event => {
    event.preventDefault();

    const { attachment, onRemove } = this.props;
    onRemove(attachment.get('blob_id'));
  };

  render() {
    const { attachment } = this.props;

    return (
      <div className="dpdesignportal-chat-form-attached-file">
        <div className="dpdesignportal-chat-form-attached-file-icon">
          <i className="fa fa-file-pdf-o"></i>
        </div>
        <div className="attached-file-title">{attachment.get('filename')} ({attachment.get('filesize_readable')})</div>
        <a href="#" className="dpdesignportal-chat-form-attached-file-remove" onClick={this.onRemove}>
          <i className="fa fa-times-circle"></i>
        </a>
      </div>
    );
  }
}
