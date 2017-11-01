import PropTypes from 'prop-types';
import React from 'react';

export class Gravatar extends React.Component {

  static propTypes = {
    children: PropTypes.node,
    size:     PropTypes.number,
    gravatar: PropTypes.string
  };

  getUrl() {
    const { gravatar, size } = this.props;
    const delimiter = gravatar.indexOf('?') === -1 ? '?' : '&';

    return `${gravatar}${delimiter}default=blank${size ? `&s=${size * 2}` : ''}`;
  }

  render() {
    const { children, size } = this.props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,

      imageUrl: this.getUrl(),
      width:    size,
      height:   size
    });
  }
}
