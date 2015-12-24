import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class UserPhoto extends React.Component {

  static propTypes = {
    type: PropTypes.string,
    width: PropTypes.number,
    height: PropTypes.number,
    imageUrl: PropTypes.string,
    text: PropTypes.string,
    children: PropTypes.node
  };

  getStyle() {
    const { imageUrl, width, height } = this.props;
    const style = {
      width: `${width}px !important`,
      height: `${height}px !important`
    };

    if (imageUrl) {
      style.backgroundImage = `url(${imageUrl})`;
    }

    return style;
  }

  getTextStyle() {
    const { width, height } = this.props;

    return {
      lineHeight: height,
      width: width,
      height: height,
      display: 'inline-block',
      textAlign: 'center'
    };
  }

  render() {
    const { type, text, children } = this.props;

    return (
      <span className={classNames('user-photo', {'text-fallback': type === 'text'})} style={this.getStyle()}>
        <span className="text" style={this.getTextStyle()}>{text}</span>
        {children}
      </span>
    );
  }
}
