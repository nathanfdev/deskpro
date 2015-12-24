import React, { PropTypes } from 'react';

export class AgentAvatar extends React.Component {

  static propTypes = {
    avatarUrl: PropTypes.string
  };

  render() {
    const { avatarUrl } = this.props;
    const style = {};
    if (avatarUrl) {
      style.backgroundImage = `url(${avatarUrl})`;
    }

    return (
      <div className="dpdesignportal-chat-header-avatar" style={style}>
        <i className="fa fa-user"></i>
      </div>
    );
  }
}
