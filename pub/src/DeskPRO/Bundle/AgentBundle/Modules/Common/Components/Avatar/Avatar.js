import React, { PropTypes } from 'react';

export class Avatar extends React.Component {
  static propTypes = {
    url: PropTypes.string,
    size: PropTypes.number,
    urlPattern: PropTypes.string,
    gravatar: PropTypes.string,
    isFallback: PropTypes.bool.isRequired,
    fallbackText: PropTypes.string.isRequired
  };

  render() {
    return this.props.isFallback ? this.renderFallbackText() : this.renderImage();
  }

  renderFallbackText() {
    return (
      <span className="user-photo text-fallback">
        <div className="text">{this.props.fallbackText}</div>
      </span>
    );
  }

  renderImage() {
    const { url, size, urlPattern, gravatar } = this.props;
    const img = size && urlPattern
              ? urlPattern.replace(/\{\{IMG_SIZE}}/, size)
              : url;

    return (
      <span className="user-photo" style={{backgroundImage: 'url(' + img + ')'}}>
        {this.renderGravatar(gravatar, size)}
      </span>
    );
  }

  renderGravatar(gravatar, size) {
    if (gravatar) {
      const gravatarImg = gravatar + '&default=blank' + (size ? '&s=' + size : '');
      return (
        <span className="user-photo" style={{backgroundImage: 'url(' + gravatarImg + ')'}} />
      );
    }
  }
}
