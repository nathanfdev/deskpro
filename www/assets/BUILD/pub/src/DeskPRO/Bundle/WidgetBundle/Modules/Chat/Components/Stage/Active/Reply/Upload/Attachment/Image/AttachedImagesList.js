import PropTypes from 'prop-types';
import React from 'react';
import { AttachedImage } from './AttachedImage';

export class AttachedImagesList extends React.Component {

  static propTypes = {
    attachedImages:     PropTypes.object,
    onRemoveAttachment: PropTypes.func
  };

  render() {
    const { attachedImages, onRemoveAttachment } = this.props;

    return (
      <div className="dpdesignportal-chat-form-attached-image-list">
        <ul>
          {attachedImages.map((attachment, index) =>
            <AttachedImage key={index}
              attachment={attachment}
              onRemove={onRemoveAttachment}
            />)}
        </ul>
      </div>
    );
  }
}
