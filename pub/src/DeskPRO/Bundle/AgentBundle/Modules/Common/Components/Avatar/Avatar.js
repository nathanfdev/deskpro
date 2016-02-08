import React, { PropTypes } from 'react';
import { ImageAvatar, Gravatar, TextAvatar } from 'DeskPRO/Component/Avatar';
import { UserPhoto } from './UserPhoto';

export class Avatar extends React.Component {

  static propTypes = {
    url: PropTypes.string,
    urlPattern: PropTypes.string,
    gravatar: PropTypes.string
  };

  render() {
    const { url, urlPattern, gravatar } = this.props;

    if (url || urlPattern) {
      return (
        <ImageAvatar {...this.props}>
          <UserPhoto />
        </ImageAvatar>
      );
    }
    if (gravatar) {
      return (
        <TextAvatar {...this.props}>
          <UserPhoto type="text">
            <Gravatar {...this.props}>
              <UserPhoto type="gravatar" />
            </Gravatar>
          </UserPhoto>
        </TextAvatar>
      );
    }

    return (
      <TextAvatar {...this.props}>
        <UserPhoto type="text" />
      </TextAvatar>
    );
  }
}
