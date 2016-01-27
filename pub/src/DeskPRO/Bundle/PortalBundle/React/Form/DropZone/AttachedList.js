import React, { PropTypes } from 'react';
import { AttachedFile } from './AttachedFile';
import { UploadingFile } from './UploadingFile';

export class AttachedList extends React.Component {

  static propTypes = {
    onDelete: PropTypes.func,
    files: PropTypes.array.isRequired
  };

  render() {
    const { files, onDelete } = this.props;
    if (!files.length) {
      return null;
    }

    return (
      <ul>
        {files.map((file, key) => file.info
            ? <AttachedFile file={file} key={key} onDelete={onDelete} />
            : <UploadingFile file={file} key={key} />
        )}
      </ul>
    );
  }
}
