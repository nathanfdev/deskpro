import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { filenameMaxLength } from 'DeskPRO/Component/Util/Filename';

export class MessageFile extends React.Component {

  static propTypes = {
    message: PropTypes.object,
    attachment: PropTypes.object
  };

  getFileIcon() {
    const { attachment } = this.props;

    switch (attachment.get('content_type')) {
      case 'application/zip':
      case 'application/x-gzip':
        return 'file-zip-o';
      case 'application/pdf':
        return 'file-pdf-o';
      case 'text/plain':
        return 'file-text-o';
      case 'text/x-php':
        return 'file-code-o';
      case 'application/msword':
        return 'file-word-o';
      default:
        return 'fa-file-o';
    }
  }

  render() {
    const { attachment } = this.props;

    return (
      <div>
        <i className={classNames('fa', this.getFileIcon())} />
        <a href={attachment.get('download_url')}>{filenameMaxLength(attachment.get('filename'), 25)}</a>
      </div>
    );
  }
}
