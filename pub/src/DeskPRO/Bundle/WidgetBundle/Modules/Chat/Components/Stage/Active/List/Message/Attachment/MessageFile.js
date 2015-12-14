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
        return 'fa-file-zip-o';
      case 'application/pdf':
        return 'fa-file-pdf-o';
      case 'text/plain':
        return 'fa-file-text-o';
      case 'text/x-php':
        return 'fa-file-code-o';
      case 'application/msword':
        return 'fa-file-word-o';
      case 'audio/mpeg':
        return 'fa-file-audio-o';
      default:
        return 'fa-file-o';
    }
  }

  render() {
    const { attachment } = this.props;

    return (
      <div className="dpdesignportal-message-content-file-attachment">
        <i className={classNames('fa', this.getFileIcon())} />

        <a href={attachment.get('download_url')}>
          {filenameMaxLength(attachment.get('filename'), 40)}
        </a>

        ({attachment.get('filesize_readable')})
      </div>
    );
  }
}
