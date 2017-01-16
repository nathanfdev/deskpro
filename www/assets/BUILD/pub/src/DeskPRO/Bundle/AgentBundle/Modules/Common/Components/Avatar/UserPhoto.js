import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { isDarkBg, colorLuminance } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';

export class UserPhoto extends React.Component {

  static propTypes = {
    type:        PropTypes.string,
    width:       PropTypes.number,
    height:      PropTypes.number,
    imageUrl:    PropTypes.string,
    text:        PropTypes.string,
    color:       PropTypes.string,
    borderColor: PropTypes.string,
    children:    PropTypes.node,
    className:   PropTypes.string
  };

  static defaultProps = {
    className: ''
  };

  getStyle() {
    const { imageUrl, color, borderColor, width, height, type } = this.props;
    const style = {
      width:          `${width}px`,
      height:         `${height}px`,
      backgroundSize: '100% 100%'
    };

    if (color) {
      style.backgroundColor = color;
    }
    if (borderColor) {
      style.borderColor = borderColor;
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
    const { width, height, color } = this.props;

    return {
      width:      `${width}px`,
      height:     `${height}px`,
      lineHeight: `${height}px`,
      display:    'inline-block',
      textAlign:  'center',
      color:      isDarkBg(color) ? colorLuminance('#fff', -0.05) : '#4c4f50'
    };
  }

  render() {
    const { type, text, children, className } = this.props;

    return (
      <span
        style={this.getStyle()}
        className={classNames(
        className,
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

export default UserPhoto;
