import PropTypes from 'prop-types';
import React from 'react';
import { AttachedFile } from './AttachedFile';

export class AttachedFiles extends React.Component {

  static propTypes = {
    attachedFiles:      PropTypes.object,
    onRemoveAttachment: PropTypes.func
  };

  render() {
    const { attachedFiles, onRemoveAttachment } = this.props;

    return (
      <div className="dropzone-container">
        {attachedFiles.map((attachment, index) =>
          <AttachedFile key={index}
            attachment={attachment}
            onRemove={onRemoveAttachment}
          />)}
      </div>
    );
  }
}
