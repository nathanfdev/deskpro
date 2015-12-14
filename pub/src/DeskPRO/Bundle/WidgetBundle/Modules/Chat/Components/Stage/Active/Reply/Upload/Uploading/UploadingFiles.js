import React, { PropTypes } from 'react';
import { UploadingFile } from './UploadingFile';

export class UploadingFiles extends React.Component {

  static propTypes = {
    files: PropTypes.array
  };

  render() {
    return (
      <div className="dropzone-container">
        {this.props.files.map((file, index) => <UploadingFile key={index} file={file} />)}
      </div>
    );
  }
}
