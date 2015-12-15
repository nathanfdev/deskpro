import React, { PropTypes } from 'react';
import { UploadingFile } from './UploadingFile';

export class UploadingFiles extends React.Component {

  static propTypes = {
    files: PropTypes.object,
    failed: PropTypes.object
  };

  render() {
    const { files, failed } = this.props;

    return (
      <div className="dropzone-container">
        {files.map((file, index) => <UploadingFile key={index}
                                                   file={file}
                                                   failed={failed.indexOf(file) !== -1} {...this.props} />)}
      </div>
    );
  }
}
