import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class MimeIcon extends React.Component {
  static propTypes = {
    mimeType: PropTypes.string.isRequired
  };

  static getIcon = (mimeType) => {
    if (mimeType.match(/^image/)) {
      return 'file image outline';
    }
    if (mimeType.match(/^audio/)) {
      return 'file audio outline';
    }
    if (mimeType.match(/^video/)) {
      return 'file video outline';
    }
    switch (mimeType) {
      case 'application/pdf':
        return 'file pdf outline';
      case 'application/msword':
      case 'application/word':
      case 'application/vnd.ms-word':
      case 'application/x-msword':
        return 'file word outline';
      case 'application/msexcel':
      case 'application/excel':
      case 'application/vnd.ms-excel':
      case 'application/x-msexcel':
        return 'file excel outline';
      case 'application/mspowerpoint':
      case 'application/powerpoint':
      case 'application/vnd.ms-powerpoint':
      case 'application/x-mspowerpoint':
        return '';
      case 'text/plain':
        return 'file text outline';
      case 'application/zip':
        return 'file archive outline';
      default:
        return 'file outline';
    }
  };

  render() {
    const { mimeType } = this.props;
    return (<i className={classNames('icon', MimeIcon.getIcon(mimeType))} />);
  }
}
