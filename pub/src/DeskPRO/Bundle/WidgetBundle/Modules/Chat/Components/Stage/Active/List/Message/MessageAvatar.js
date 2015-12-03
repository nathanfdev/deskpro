import React, { PropTypes } from 'react';

export class MessageAvatar extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const { message } = this.props;
    const avatarUrl = message && message.get('author_avatar');
    const style = {};

    if (avatarUrl) {
      style.backgroundImage = `url(${avatarUrl})`;
    }

    return (
      <div className="dpdesignportal-message-avatar" style={style}>
        <i className="fa fa-user"></i>
      </div>
    );
  }
}
