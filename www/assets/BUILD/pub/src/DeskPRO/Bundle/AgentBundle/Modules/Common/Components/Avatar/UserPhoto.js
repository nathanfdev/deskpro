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
    children: PropTypes.node,
    classes:  PropTypes.array,
  };

  static defaultProps = {
    classes: []
  };

  getStyle() {
    const { imageUrl, color, width, height, type } = this.props;
    const style = {
      width:          `${width}px`,
      height:         `${height}px`,
      backgroundSize: '100% 100%'
    };

    if (color) {
      style.backgroundColor = color;
    }
    if (imageUrl) {
      style.backgroundImage = `url(${imageUrl})`;
    }
    if (type !== 'gravatar') {
      style.display = 'inline-block';
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
    const { type, text, children, classes } = this.props;

    return (
      <span
        style={this.getStyle()}
        className={classNames(
        classes,
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
