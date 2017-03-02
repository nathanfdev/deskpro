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
    className:   PropTypes.string,
    title:       PropTypes.string,
    noOffline:   PropTypes.bool
  };

  static defaultProps = {
    className: ''
  };

  getStyle() {
    const { imageUrl, width, height, type } = this.props;
    const style = {
      width:          `${width}px`,
      height:         `${height}px`,
      backgroundSize: '100% 100%'
    };


    if (imageUrl) {
      style.backgroundImage = `url(${imageUrl})`;
    }
    if (type !== 'gravatar') {
      style.display = 'inline-block';
    }

    return style;
  }

  getTextStyle() {
    const { width, height, color, borderColor } = this.props;

    const style = {
      width:      `${width}px`,
      height:     `${height}px`,
      lineHeight: `${height}px`,
      display:    'inline-block',
      textAlign:  'center',
      color:      isDarkBg(color) ? colorLuminance('#fff', -0.1) : '#4c4f50'
    };

    if (color) {
      style.backgroundColor = color;
    }
    if (borderColor) {
      style.borderColor = borderColor;
    }

    return style;
  }

  render() {
    const { type, text, children, className, title, width, height, noOffline } = this.props;

    const spanProps = {
      style:     this.getStyle(),
      className: classNames(className, 'user-photo',
        {
          'text-fallback': type === 'text',
          gravatar:        type === 'gravatar'
        })
    };

    if (title) {
      spanProps.title = title;
    }

    const offlineProps = {
      width:        width + 2,
      height:       height + 2,
      lineHeight:   height + 2,
      textAlign:    'center',
      position:     'absolute',
      top:          '-1px',
      left:         '-1px',
      borderRadius: '500rem'
    };

    return (
      <span {...spanProps}>
        {text && <span className={classNames('text', className)} style={this.getTextStyle()}>{text}</span>}
        {children}
        { !noOffline ? <span className="offline-block" style={offlineProps} /> : null }
      </span>
    );
  }
}

export default UserPhoto;
