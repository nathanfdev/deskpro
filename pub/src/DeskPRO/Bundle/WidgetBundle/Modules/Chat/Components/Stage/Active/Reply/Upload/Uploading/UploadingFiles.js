import React, { PropTypes } from 'react';
import { UploadingFile } from './UploadingFile';

export class UploadingFiles extends React.Component {

  static propTypes = {
    uploadingFiles: PropTypes.object
  };

  render() {
    return (
      <div className="dropzone-container">
        {this.props.uploadingFiles.map((file, index) => <UploadingFile key={index} file={file} />)}
      </div>
    );
  }
}
