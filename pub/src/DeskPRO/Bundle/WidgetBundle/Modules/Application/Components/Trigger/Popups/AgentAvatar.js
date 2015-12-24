import React, { PropTypes } from 'react';

export class AgentAvatar extends React.Component {

  static propTypes = {
    imageUrl: PropTypes.object
  };

  render() {
    const { imageUrl } = this.props;
    const style = {};
    if (imageUrl) {
      style.backgroundImage = `url(${imageUrl})`;
    }

    return (
      <div className="dpdesignportal-chat-header-avatar" style={style}>
        {!imageUrl && <i className="fa fa-user"></i>}
      </div>
    );
  }
}
