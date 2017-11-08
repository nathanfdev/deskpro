import PropTypes from 'prop-types';
import React from 'react';
import { AttachedFile } from './AttachedFile';
import { UploadingFile } from './UploadingFile';

export class AttachedList extends React.Component {

  static propTypes = {
    onDelete:  PropTypes.func,
    inputName: PropTypes.string,
    files:     PropTypes.array.isRequired
  };

  render() {
    const { files, inputName, onDelete } = this.props;
    if (!files.length) {
      return null;
    }

    return (
      <ul>
        {files.map((file, key) => (file.info
            ? <AttachedFile file={file} key={key} inputName={inputName} onDelete={onDelete} />
            : <UploadingFile file={file} key={key} />
        ))}
      </ul>
    );
  }
}
