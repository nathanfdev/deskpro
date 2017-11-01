import PropTypes from 'prop-types';
import React from 'react';
import { UploadingFile } from './UploadingFile';

export class UploadingFiles extends React.Component {

  static propTypes = {
    files:  PropTypes.object,
    failed: PropTypes.object
  };

  render() {
    const { files, failed } = this.props;

    return (
      <div className="dropzone-container">
        {files.map((file, index) =>
          <UploadingFile key={index}
            file={file}
            isFailed={failed.indexOf(file) !== -1} {...this.props}
          />)}
      </div>
    );
  }
}
