import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { filenameMaxLength, getFileIcon } from 'DeskPRO/Component/Util/Filename';

export class MessageFile extends React.Component {

  static propTypes = {
    message:    PropTypes.object,
    attachment: PropTypes.object
  };

  render() {
    const { attachment } = this.props;

    return (
      <div className="dpdesignportal-message-content-file-attachment">
        <i className={classNames('fa', getFileIcon(attachment.get('content_type')))} />

        <a href={`${attachment.get('download_url')}?dl=1`} target="_blank">
          {filenameMaxLength(attachment.get('filename'), 30)}
        </a>

        ({attachment.get('filesize_readable')})
      </div>
    );
  }
}
