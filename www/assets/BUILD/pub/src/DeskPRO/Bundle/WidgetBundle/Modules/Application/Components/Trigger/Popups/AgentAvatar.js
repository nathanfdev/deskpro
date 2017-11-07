import PropTypes from 'prop-types';
import React from 'react';

export class AgentAvatar extends React.Component {

  static propTypes = {
    imageUrl:     PropTypes.string,
    disconnected: PropTypes.bool
  };

  render() {
    const { imageUrl, disconnected } = this.props;
    const style = {};
    if (imageUrl) {
      style.backgroundImage = `url(${imageUrl})`;
    }

    return (
      <div className="dpdesignportal-chat-header-avatar">
        <i className="fa fa-user" />
        {imageUrl && <div className="avatar-img" style={style}></div>}
        {disconnected &&
          <span className="dpdesignportal-chat-header-avatar-disconnected">
            <i className="fa fa-plug" />
          </span>
        }
      </div>
    );
  }
}
