import PropTypes from 'prop-types';
import React from 'react';

export class MessageAvatar extends React.Component {

  static propTypes = {
    imageUrl:     PropTypes.string,
    primaryColor: PropTypes.string
  };

  render() {
    const { imageUrl, primaryColor } = this.props;
    const style = {};

    if (imageUrl) {
      style.backgroundImage = `url(${imageUrl})`;
    }
    if (primaryColor) {
      style.backgroundColor = primaryColor;
    }

    return (
      <div className="dpdesignportal-message-avatar" style={style}>
        {!imageUrl && <i className="fa fa-user" />}
      </div>
    );
  }
}
