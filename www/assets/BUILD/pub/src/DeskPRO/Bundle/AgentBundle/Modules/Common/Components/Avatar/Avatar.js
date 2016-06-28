import React, { PropTypes } from 'react';
import { ImageAvatar, Gravatar, TextAvatar } from 'DeskPRO/Component/Avatar';
import { UserPhoto } from './UserPhoto';

export class Avatar extends React.Component {

  static propTypes = {
    size:       PropTypes.number,
    url:        PropTypes.string,
    urlPattern: PropTypes.string,
    gravatar:   PropTypes.string
  };

  render() {
    const { url, urlPattern, gravatar, size } = this.props;

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
            <Gravatar gravatar={gravatar} size={size}>
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
