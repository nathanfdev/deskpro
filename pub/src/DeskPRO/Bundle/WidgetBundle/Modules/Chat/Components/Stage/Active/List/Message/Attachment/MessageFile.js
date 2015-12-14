import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class MessageFile extends React.Component {

  static propTypes = {
    message: PropTypes.object,
    attachment: PropTypes.object
  };

  getFileIcon() {
    const { attachment } = this.props;

    switch (attachment.get('content_type')) {
      default:
        return 'file-o';
    }

    //file
    //file-archive-o
    //file-audio-o
    //file-code-o
    //file-excel-o
    //file-image-o
    //file-movie-o
    //file-o
    //file-pdf-o
    //file-photo-o
    //file-picture-o
    //file-powerpoint-o
    //file-sound-o
    //file-text
    //file-text-o
    //file-video-o
    //file-word-o
    //file-zip-o
  }

  render() {
    const { attachment } = this.props;

    return (
      <div>
        <i className={classNames('fa', this.getFileIcon())} />
        <a href={attachment.get('download_url')}>{attachment.get('filename')}</a>
      </div>
    );
  }
}
