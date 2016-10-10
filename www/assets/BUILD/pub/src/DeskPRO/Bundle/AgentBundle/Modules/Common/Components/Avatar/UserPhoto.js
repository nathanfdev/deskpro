import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class UserPhoto extends React.Component {

  static propTypes = {
    type:     PropTypes.string,
    width:    PropTypes.number,
    height:   PropTypes.number,
    imageUrl: PropTypes.string,
    text:     PropTypes.string,
    color:    PropTypes.string,
    children: PropTypes.node
  };

  getStyle() {
    const { imageUrl, color, width, height } = this.props;
    const style = {
      width:   `${width}px`,
      height:  `${height}px`
    };

    if (color) {
      style.backgroundColor = color;
    }
    if (imageUrl) {
      style.backgroundImage = `url(${imageUrl})`;
    }

    return style;
  }

  getTextStyle() {
    const { width, height } = this.props;

    return {
      width:      `${width}px`,
      height:     `${height}px`,
      lineHeight: `${height}px`,
      display:    'inline-block',
      textAlign:  'center'
    };
  }

  render() {
    const { type, text, children } = this.props;

    return (
      <span
        style={this.getStyle()}
        className={classNames(
        'user-photo', {
          'text-fallback': type === 'text',
          gravatar:        type === 'gravatar'
        })}
      >

        {text && <span className="text" style={this.getTextStyle()}>{text}</span>}
        {children}
      </span>
    );
  }
}
