import React, { PropTypes } from 'react';
import { AttachedImage } from './AttachedImage';

export class AttachedImagesList extends React.Component {

  static propTypes = {
    attachments: PropTypes.object,
    onRemoveFile: PropTypes.func
  };

  render() {
    const { attachments, onRemoveFile } = this.props;

    return (
      <div className="dpdesignportal-chat-form-attached-image-list">
        <ul>
          {attachments.map((attachment, index) => <AttachedImage key={index}
                                                                 attachment={attachment}
                                                                 onRemove={onRemoveFile} />)}
        </ul>
      </div>
    );
  }
}
