import React, { PropTypes } from 'react';

export class AgentAvatar extends React.Component {

  static propTypes = {
    imageUrl: PropTypes.object,
    disconnected: PropTypes.bool
  };

  render() {
    const { imageUrl, disconnected } = this.props;
    const style = {};
    if (imageUrl) {
      style.backgroundImage = `url(${imageUrl})`;
    }

    return (
      <div className="dpdesignportal-chat-header-avatar" style={style}>
        {!imageUrl && <i className="fa fa-user"></i>}
        {disconnected &&
          <span className="dpdesignportal-chat-header-avatar-disconnected">
            <i className="fa fa-plug"/>
          </span>
        }
      </div>
    );
  }
}
