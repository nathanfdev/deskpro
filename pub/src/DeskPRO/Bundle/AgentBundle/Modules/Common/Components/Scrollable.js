import React, { PropTypes } from 'react';
import ScrollArea from 'react-scrollbar';

export class Scrollable extends React.Component {
  static propTypes = {
    children: PropTypes.node,
    horizontal: PropTypes.bool,
    vertical: PropTypes.bool,
    style: PropTypes.object,
    contentStyle: PropTypes.object,
  };

  render() {
    return this.platformSupportsScrollbars() ? this.renderWithScrollbars() : this.renderWithoutScrollbars();
  }

  // @todo how can we determine if current platform supports scrollbars?
  platformSupportsScrollbars() {
    return true;
  }

  renderWithScrollbars() {
    const defaultStyle = {height: '100%', width: '100%'};
    const customStyle = this.props.style;
    const style = {...defaultStyle, ...customStyle};

    const defaultContentStyle = {height: 'auto', width: 'auto'};
    const customContentStyle = this.props.contentStyle;
    const contentStyle = {...defaultContentStyle, ...customContentStyle};

    return (
      <ScrollArea
        style={style}
        contentStyle={contentStyle}
        horizontal={this.props.horizontal || false}
        vertical={this.props.vertical || false}
      >
        {this.props.children}
      </ScrollArea>
    );
  }

  renderWithoutScrollbars() {
    return (
      <div>{this.props.children}</div>
    );
  }
}
