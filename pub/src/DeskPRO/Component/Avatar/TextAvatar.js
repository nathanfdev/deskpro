import React, { PropTypes } from 'react';

export class TextAvatar extends React.Component {

  static propTypes = {
    children: PropTypes.node,
    size: PropTypes.number,
    text: PropTypes.string
  };

  render() {
    const { text, size, children } = this.props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,

      text,
      width: size,
      height: size
    });
  }
}
