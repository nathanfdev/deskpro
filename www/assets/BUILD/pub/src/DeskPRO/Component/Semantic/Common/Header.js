import React, { PropTypes } from 'react';
import classNames from 'classnames';

class Header extends React.Component {
  static propTypes = {
    size:    PropTypes.number.isRequired,
    content: PropTypes.string.isRequired,
    classes: PropTypes.array
  };

  static defaultProps = {
    classes: [],
    size: 3
  };

  render() {
    const { size, content, classes } = this.props;
    const element = `h${size}`;
    return React.createElement(element, { className: classNames(['ui', 'header'], classes)}, content);
  }
}

export default Header;
