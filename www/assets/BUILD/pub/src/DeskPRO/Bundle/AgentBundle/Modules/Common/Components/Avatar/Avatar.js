import PropTypes from 'prop-types';
import React from 'react';
import { ImageAvatar, Gravatar, TextAvatar } from 'DeskPRO/Component/Avatar';
import { UserPhoto } from './UserPhoto';

export class Avatar extends React.Component {

  static propTypes = {
    size:       PropTypes.number,
    url:        PropTypes.string,
    urlPattern: PropTypes.string,
    gravatar:   PropTypes.string,
    className:  PropTypes.string
  };

  static defaultProps = {
    className: []
  };

  render() {
    const { url, urlPattern, gravatar, size, className } = this.props;

    if (url || urlPattern) {
      return (
        <ImageAvatar {...this.props}>
          <UserPhoto className={className} />
        </ImageAvatar>
      );
    }
    if (gravatar) {
      return (
        <TextAvatar {...this.props}>
          <UserPhoto type="text" className={className}>
            <Gravatar gravatar={gravatar} size={size}>
              <UserPhoto type="gravatar" className={className} />
            </Gravatar>
          </UserPhoto>
        </TextAvatar>
      );
    }

    return (
      <TextAvatar {...this.props}>
        <UserPhoto type="text" className={className}  />
      </TextAvatar>
    );
  }
}

export default Avatar;
