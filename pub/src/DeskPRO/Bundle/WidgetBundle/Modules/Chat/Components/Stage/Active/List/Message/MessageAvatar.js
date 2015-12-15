import React, { PropTypes } from 'react';

export class MessageAvatar extends React.Component {

  static propTypes = {
    url: PropTypes.string
  };

  render() {
    const { url } = this.props;
    const style = {};
    if (url) {
      style.backgroundImage = `url(${url})`;
    }

    return (
      <div className="dpdesignportal-message-avatar" style={style}>
        <i className="fa fa-user"></i>
      </div>
    );
  }
}
