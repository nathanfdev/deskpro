import React, { PropTypes } from 'react';

export class TextAvatar extends React.Component {

  static propTypes = {
    children: PropTypes.node,
    size: PropTypes.number,
    text: PropTypes.string,
    color: PropTypes.string
  };

  render() {
    const { text, color, size, children } = this.props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,

      text,
      color,
      width: size,
      height: size
    });
  }
}
