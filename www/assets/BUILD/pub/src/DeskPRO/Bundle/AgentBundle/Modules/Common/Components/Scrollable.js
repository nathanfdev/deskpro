import PropTypes from 'prop-types';
import React from 'react';
import ScrollArea from 'react-scrollbar';
import classNames from 'classnames';

// NOTE
// - <Scrollable> can only be used when the parent
// element is positioned and has dimensions. Scrollable will fill the parent
// entirely.
// - So you must set width/height and at least position:relative.

export class Scrollable extends React.Component {

  static propTypes = {
    children:   PropTypes.node,
    both:       PropTypes.bool,
    horizontal: PropTypes.bool,
    vertical:   PropTypes.bool,
    className:  PropTypes.string,
    onScroll:   PropTypes.func
  };

  static defaultProps = {
    onScroll() {

    }
  };

  // @todo how can we determine if current platform supports scrollbars?
  static platformSupportsScrollbars() {
    return true;
  }

  componentWillReceiveProps() {
    this.dirty = true;
  }

  shouldComponentUpdate() {
    const dirty = this.dirty || true;
    this.dirty = false;
    return dirty;
  }

  scrollBottom() {
    if (this.scrollarea) {
      this.scrollarea.scrollBottom();
    }
  }

  renderWithScrollbars() {
    const { horizontal, vertical, both, className, children, onScroll } = this.props;

    return (
      <div
        className={classNames(
          'dp-scrollable',
          'as-js-scrollbar',
          className,
          {
            'as-horizontal': horizontal || both,
            'as-vertical':   vertical || both
          }
      )}
      >
        <ScrollArea
          ref={(c) => { this.scrollarea = c; }}
          className="dpscrollarea"
          contentClassName="dpscrollarea"
          horizontal={horizontal || both}
          vertical={vertical || both}
          onScroll={onScroll}
        >

          {children}
        </ScrollArea>
      </div>
    );
  }

  renderWithoutScrollbars() {
    const { horizontal, vertical, both, className, children } = this.props;

    return (
      <div
        className={classNames(
          'dp-scrollable',
          'as-native-scrollbar',
          className,
          {
            'as-horizontal': horizontal || both,
            'as-vertical':   vertical || both
          }
        )}
      >
        {children}
      </div>
    );
  }

  render() {
    return Scrollable.platformSupportsScrollbars() ? this.renderWithScrollbars() : this.renderWithoutScrollbars();
  }
}

export default Scrollable;
