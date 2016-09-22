import React, { PropTypes } from 'react';
import classNames from 'classnames';

class Header extends React.Component {
  static propTypes = {
    level:   PropTypes.number.isRequired,
    content: PropTypes.oneOfType([PropTypes.string, PropTypes.object]).isRequired,
    classes: PropTypes.array
  };

  static defaultProps = {
    classes: [],
    level:   3
  };

  render() {
    const { level, content, classes } = this.props;
    const element = `h${level}`;
    return React.createElement(element, { className: classNames(['ui', 'header'], classes) }, content);
  }
}

export default Header;
