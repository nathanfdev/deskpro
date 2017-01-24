import React, { PropTypes } from 'react';

export class ImageAvatar extends React.Component {

  static propTypes = {
    children:   PropTypes.node,
    size:       PropTypes.number,
    url:        PropTypes.string,
    urlPattern: PropTypes.string,
    title:      PropTypes.string
  };

  getUrl() {
    const { size, url, urlPattern } = this.props;
    // just increase avatar size 4 times, so we don't be hit issue when gd scales it bad
    return size && urlPattern ? urlPattern.replace(/\{\{IMG_SIZE}}/, size * 4) : url;
  }

  render() {
    const { size, children, title } = this.props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,
      title,
      imageUrl: this.getUrl(),
      width:    size,
      height:   size
    });
  }
}
