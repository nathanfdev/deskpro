import React, { PropTypes } from 'react';
import { AttachedFile } from './AttachedFile';

export class AttachedFiles extends React.Component {

  static propTypes = {
    attachments: PropTypes.object
  };

  onRemoveFile = fileId => {
    console.log('onRemoveFile ' + fileId);
  };

  render() {
    return (
      <div className="dropzone-container">
        {this.props.attachments.map((attachment, index) => <AttachedFile key={index}
                                                                         attachment={attachment}
                                                                         onRemove={this.onRemoveFile} />)}
      </div>
    );
  }
}
