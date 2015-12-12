import React, { PropTypes } from 'react';
import { AttachedFile } from './AttachedFile';

export class AttachedFiles extends React.Component {

  static propTypes = {
    attachments: PropTypes.object,
    onRemoveFile: PropTypes.func
  };

  render() {
    const { attachments, onRemoveFile } = this.props;

    return (
      <div className="dropzone-container">
        {attachments.map((attachment, index) => <AttachedFile key={index}
                                                              attachment={attachment}
                                                              onRemove={onRemoveFile} />)}
      </div>
    );
  }
}
