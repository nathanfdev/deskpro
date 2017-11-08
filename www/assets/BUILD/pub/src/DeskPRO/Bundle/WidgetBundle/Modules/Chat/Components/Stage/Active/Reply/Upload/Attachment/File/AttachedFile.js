import PropTypes from 'prop-types';
import React from 'react';
import { filenameMaxLength } from 'DeskPRO/Component/Util/Filename';

export class AttachedFile extends React.Component {

  static propTypes = {
    attachment: PropTypes.object,
    onRemove:   PropTypes.func
  };

  onRemove = event => {
    event.preventDefault();

    const { attachment, onRemove } = this.props;
    onRemove(attachment);
  };

  render() {
    const { attachment } = this.props;

    return (
      <div className="dpdesignportal-chat-form-attached-file">
        <div className="dpdesignportal-chat-form-attached-file-icon">
          <i className="fa fa-file-pdf-o" />
        </div>
        <div className="attached-file-title">
          {filenameMaxLength(attachment.get('filename'), 30)} ({attachment.get('filesize_readable')})
        </div>
        <a href="#" className="dpdesignportal-chat-form-attached-file-remove" onClick={this.onRemove}>
          <i className="fa fa-times-circle" />
        </a>
      </div>
    );
  }
}
