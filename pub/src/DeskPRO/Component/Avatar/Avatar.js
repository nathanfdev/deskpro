import React, { PropTypes } from 'react';

export class Avatar extends React.Component {

  static propTypes = {
    url: PropTypes.string,
    size: PropTypes.any.isRequired,
    fallbackText: PropTypes.string.isRequired,
    color: PropTypes.string,
    urlPattern: PropTypes.string,
    defaultUrlPattern: PropTypes.string,
    gravatar: PropTypes.string
  };

  getImg() {
    const { url, size, urlPattern } = this.props;

    return size && urlPattern
      ? urlPattern.replace(/\{\{IMG_SIZE}}/, size)
      : url;
  }

  getStyle(backgroundImage) {
    const style = {
      display: 'inline-block',
      marginRight: '5px'
    };

    if (backgroundImage) {
      style.backgroundImage = backgroundImage;
    }
    if (this.props.size) {
      style.width = style.height = this.props.size + ' !important';
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

  renderImage(content = '') {
    return (
      <span className="user-photo" style={this.getStyle('url(' + this.getImg() + ')')}>
        <span className="text" style={this.getTextStyle()}>&nbsp;</span>
        {content}
      </span>
    );
  }

  renderGravatar() {
    const { gravatar, size } = this.props;
    const delimiter = gravatar.indexOf('?') === -1 ? '?' : '&';
    const gravatarImg = gravatar + delimiter + 'default=blank' + (size ? '&s=' + size : '');
    const gravatarStyle = this.getStyle('url(' + gravatarImg + ')');
    gravatarStyle.position = 'absolute';
    gravatarStyle.top = '0';
    gravatarStyle.left = '0';
    const gravatarContent = (<span className="user-photo gravatar" style={gravatarStyle} />);

    return this.props.urlPattern ? this.renderImage(gravatarContent) : this.renderFallbackText(gravatarContent);
  }

  renderFallbackText(content = '') {
    const style = this.getStyle();
    if (this.props.color) {
      style.backgroundColor = this.props.color;
    }

    return (
      <span className="user-photo text-fallback" style={style}>
        <span className="text" style={this.getTextStyle()}>{this.props.fallbackText}</span>
        {content}
      </span>
    );
  }

  render() {
    if (this.props.urlPattern) {
      return this.renderImage();
    }
    if (this.props.gravatar) {
      return this.renderGravatar();
    }

    return this.renderFallbackText();
  }
}
