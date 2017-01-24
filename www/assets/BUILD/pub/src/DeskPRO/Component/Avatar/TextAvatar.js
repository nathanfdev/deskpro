import React, { PropTypes } from 'react';

export class TextAvatar extends React.Component {

  static propTypes = {
    children:    PropTypes.node,
    size:        PropTypes.number,
    text:        PropTypes.string,
    color:       PropTypes.string,
    borderColor: PropTypes.string,
    title:       PropTypes.string
  };

  render() {
    const { text, color, borderColor, size, children, title } = this.props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,
      text,
      color,
      title,
      borderColor,
      width:  size,
      height: size
    });
  }
}
