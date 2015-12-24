import React, { PropTypes } from 'react';
import { ImageAvatar } from 'DeskPRO/Component/Avatar/ImageAvatar';
import { UserPhoto } from './UserPhoto';

export class Avatar extends React.Component {

  static propTypes = {
    size: PropTypes.any.isRequired,
    url: PropTypes.string,
    urlPattern: PropTypes.string,
    gravatar: PropTypes.string,
    color: PropTypes.string,
    fallbackText: PropTypes.string.isRequired
  };

  getStyle(backgroundImage) {
    const { size } = this.props;
    const style = {};

    if (backgroundImage) {
      style.backgroundImage = backgroundImage;
    }
    if (size) {
      style.width = style.height = size + ' !important';
    }

    return style;
  }

  getTextStyle() {
    const size = this.props.size + 'px';

    return {
      lineHeight: size,
      width: size,
      height: size,
      display: 'inline-block',
      textAlign: 'center'
    };
  }

  renderGravatar() {
    const { gravatar, size } = this.props;
    const delimiter = gravatar.indexOf('?') === -1 ? '?' : '&';
    const gravatarImg = gravatar + delimiter + 'default=blank' + (size ? '&s=' + size : '');
    const gravatarStyle = this.getStyle('url(' + gravatarImg + ')');
    gravatarStyle.position = 'absolute';
    gravatarStyle.top = '0';
    gravatarStyle.left = '0';
    const gravatarContent = <span className="user-photo gravatar" style={gravatarStyle} />;

    return this.renderFallbackText(gravatarContent);
  }

  renderFallbackText(content = '') {
    const { color, fallbackText } = this.props;
    const style = this.getStyle();

    if (color) {
      style.backgroundColor = color;
    }

    return (
      <span className="user-photo text-fallback" style={style}>
        <span className="text" style={this.getTextStyle()}>{fallbackText}</span>
        {content}
      </span>
    );
  }

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
      return this.renderGravatar();
    }

    return this.renderFallbackText();
  }
}
