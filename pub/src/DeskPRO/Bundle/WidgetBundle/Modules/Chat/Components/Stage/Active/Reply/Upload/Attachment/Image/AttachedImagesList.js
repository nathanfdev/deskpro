import React, { PropTypes } from 'react';
import { AttachedImage } from './AttachedImage';

export class AttachedImagesList extends React.Component {

  static propTypes = {
    attachedImages: PropTypes.object,
    onRemoveFile: PropTypes.func
  };

  render() {
    const { attachedImages, onRemoveFile } = this.props;

    return (
      <div className="dpdesignportal-chat-form-attached-image-list">
        <ul>
          {attachedImages.map((attachment, index) => <AttachedImage key={index}
                                                                    attachment={attachment}
                                                                    onRemove={onRemoveFile} />)}
        </ul>
      </div>
    );
  }
}
