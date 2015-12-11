import React from 'react';
import { AttachedFile } from './AttachedFile';

export class AttachedFilesContainer extends React.Component {

  onRemoveFile = fileId => {
    console.log('onRemoveFile ' + fileId);
  };

  render() {
    const componentConfig = {
      postUrl: `${window.DP_HELPDESK_URL}/portal/api/blobs`
    };

    const djsConfig = {
      autoQueue: false,
      maxFiles: 1,
      previewsContainer: false
    };

    return (
      <div className="dropzone-container">
        <div className="dz-message">
          <AttachedFile fileId={1} name="file_name_lorem_ipsum.pdf" onRemove={this.onRemoveFile} />
          <AttachedFile fileId={2} name="file_name_lorem_ipsum.pdf" onRemove={this.onRemoveFile} />
          <AttachedFile fileId={3} name="file_name_lorem_ipsum.pdf" onRemove={this.onRemoveFile} />
          </div>
      </div>
    );
  }
}
