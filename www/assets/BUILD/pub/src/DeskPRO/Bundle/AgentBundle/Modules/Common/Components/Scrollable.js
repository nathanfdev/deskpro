import React, { PropTypes } from 'react';
import ScrollArea from 'react-scrollbar-iframe';
import classNames from 'classnames';

// NOTE
// - <Scrollable> can only be used when the parent
// element is positioned and has dimensions. Scrollable will fill the parent
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

  // @todo how can we determine if current platform supports scrollbars?
  platformSupportsScrollbars() {
    return true;
  }

  componentWillReceiveProps(props) {
    this.dirty = true;
  }

  shouldComponentUpdate() {
    const dirty = this.dirty;
    this.dirty = false;
    return dirty;
  }

  renderWithScrollbars() {
    const { horizontal, vertical, both, className, children } = this.props;

    return (
      <div className={classNames(
        'dp-scrollable',
        'as-js-scrollbar',
        className,
        {
          'as-horizontal': horizontal || both,
          'as-vertical': vertical || both
        }
      )}>
        <ScrollArea className="dpscrollarea"
                    contentClassName="dpscrollarea"
                    horizontal={horizontal || both}
                    vertical={vertical || both}>

          {children}
        </ScrollArea>
      </div>
    );
  }

  renderWithoutScrollbars() {
    const { horizontal, vertical, both, className, children } = this.props;

    return (
      <div className={classNames(
        'dp-scrollable',
        'as-native-scrollbar',
        className,
        {
          'as-horizontal': horizontal || both,
          'as-vertical': vertical || both
        }
      )}>
        {children}
      </div>
    );
  }

  render() {
    return this.platformSupportsScrollbars() ? this.renderWithScrollbars() : this.renderWithoutScrollbars();
  }
}
