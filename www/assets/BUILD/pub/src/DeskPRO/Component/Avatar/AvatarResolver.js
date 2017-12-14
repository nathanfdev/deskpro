import PropTypes from 'prop-types';
import React from 'react';
import { ImageAvatar } from './ImageAvatar';
import { Gravatar } from './Gravatar';

export class AvatarResolver extends React.Component {

  static propTypes = {
    size:     PropTypes.number,
    avatar:   PropTypes.object,
    children: PropTypes.node
  };

  render() {
    const { size, avatar, children } = this.props;

    const urlPattern = avatar && avatar.get('url_pattern');
    const gravatar = avatar && avatar.get('base_gravatar_url');
    const defaultUrlPattern = avatar.get('default_url_pattern');
    const avatarProps = { size, urlPattern, gravatar, defaultUrlPattern };

    const childContent = React.cloneElement(children, {
      ...children.props,
      avatarProps,
      defaultUrl: defaultUrlPattern && defaultUrlPattern.replace(/\{\{IMG_SIZE}}/, size * 4)
    });

    if (urlPattern) {
      return (
        <ImageAvatar {...avatarProps}>
          {childContent}
        </ImageAvatar>
      );
    }
    if (gravatar) {
      return (
        <Gravatar {...avatarProps}>
          {childContent}
        </Gravatar>
      );
    }

    return childContent;
  }
}
