import React, { PropTypes } from 'react';

export class MessageFile extends React.Component {

  static propTypes = {
    message: PropTypes.object,
    attachment: PropTypes.object
  };

  render() {
    const { attachment } = this.props;

    return (
      <div>
        File <a href={attachment.get('download_url')}>{attachment.get('filename')}</a>
      </div>
    );
  }
}
