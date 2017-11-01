import PropTypes from 'prop-types';
import React from 'react';

export class UploadingFile extends React.Component {

  static propTypes = {
    file: PropTypes.object.isRequired
  };

  render() {
    const { file } = this.props;
    return (
      <li>
        {file.file.name} (uploading...)
      </li>
    );
  }
}
