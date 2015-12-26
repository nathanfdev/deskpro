import React, { PropTypes } from 'react';

export class MessageAvatar extends React.Component {

  static propTypes = {
    imageUrl: PropTypes.string
  };

  render() {
    const { imageUrl } = this.props;
    const style = {};
    if (imageUrl) {
      style.backgroundImage = `url(${imageUrl})`;
    }

    return (
      <div className="dpdesignportal-message-avatar" style={style}>
        {!imageUrl && <i className="fa fa-user" />}
      </div>
    );
  }
}
