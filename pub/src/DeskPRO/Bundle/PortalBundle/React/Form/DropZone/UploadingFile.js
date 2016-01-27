import React, { PropTypes } from 'react';

export class UploadingFile extends React.Component {

  static propTypes = {
    file: PropTypes.object.isRequired
  };

  render() {
    const { file } = this.props;

    return (
      <li>
        <a href={file.url}>
          {file.filename}
        </a>
      </li>
    );
  }
}
