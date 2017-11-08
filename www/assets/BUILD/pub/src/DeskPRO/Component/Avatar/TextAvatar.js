import PropTypes from 'prop-types';
import React from 'react';

export class TextAvatar extends React.Component {

  static propTypes = {
    children:    PropTypes.node,
    size:        PropTypes.number,
    text:        PropTypes.string,
    color:       PropTypes.string,
    borderColor: PropTypes.string,
    title:       PropTypes.string,
    tooltipId:   PropTypes.string
  };

  render() {
    const { text, color, borderColor, size, children, title, tooltipId } = this.props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,
      text,
      color,
      title,
      tooltipId,
      borderColor,
      width:  size,
      height: size
    });
  }
}
