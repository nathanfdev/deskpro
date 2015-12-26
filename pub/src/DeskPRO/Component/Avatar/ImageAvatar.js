import React, { PropTypes } from 'react';

export class ImageAvatar extends React.Component {

  static propTypes = {
    children: PropTypes.node,
    size: PropTypes.number,
    url: PropTypes.string,
    urlPattern: PropTypes.string
  };

  getUrl() {
    const { size, url, urlPattern } = this.props;
    return size && urlPattern ? urlPattern.replace(/\{\{IMG_SIZE}}/, size) : url;
  }

  render() {
    const { size, children } = this.props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,

      imageUrl: this.getUrl(),
      width: size,
      height: size
    });
  }
}
