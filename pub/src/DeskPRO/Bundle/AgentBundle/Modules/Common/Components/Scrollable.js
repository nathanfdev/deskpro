import React, { PropTypes } from 'react';
import ScrollArea from 'react-scrollbar';
import classNames from 'classnames';

// NOTE
// - <Scrollable> can only be used when the parent
// element is positioned and has dimentions. Scrollable will fill the parent
// entirely.
// - So you must set width/height and at least position:relative.

export class Scrollable extends React.Component {
  static propTypes = {
    children: PropTypes.node,
    both: PropTypes.bool,
    horizontal: PropTypes.bool,
    vertical: PropTypes.bool,
    className: PropTypes.string
  };

  render() {
    return this.platformSupportsScrollbars() ? this.renderWithScrollbars() : this.renderWithoutScrollbars();
  }

  // @todo how can we determine if current platform supports scrollbars?
  platformSupportsScrollbars() {
    return true;
  }

  renderWithScrollbars() {
    const className = classNames('dp-scrollable', 'as-js-scrollbar', {
      'as-horizontal': this.props.horizontal || this.props.both,
      'as-vertical': this.props.vertical || this.props.both
    }, this.props.className);
    return (
      <div className={className}>
        <ScrollArea
          className="dpscrollarea"
          contentClassName="dpscrollarea"
          horizontal={this.props.horizontal || this.props.both}
          vertical={this.props.vertical || this.props.both}
        >
          {this.props.children}
        </ScrollArea>
      </div>
    );
  }

  renderWithoutScrollbars() {
    const className = classNames('dp-scrollable', 'as-native-scrollbar', {
      'as-horizontal': this.props.horizontal || this.props.both,
      'as-vertical': this.props.vertical || this.props.both
    }, this.props.className);
    return (
      <div className={className}>{this.props.children}</div>
    );
  }
}
