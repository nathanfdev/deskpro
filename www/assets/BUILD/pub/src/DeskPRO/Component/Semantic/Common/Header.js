import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class Header extends React.Component {
  static propTypes = {
    level:     PropTypes.number,
    content:   PropTypes.oneOfType([PropTypes.string, PropTypes.object]).isRequired,
    className: PropTypes.string
  };

  static defaultProps = {
    className: '',
    level:     3
  };

  render() {
    const { level, content, className } = this.props;
    const element = `h${level}`;
    return React.createElement(element, { className: classNames(['ui', 'header'], className) }, content);
  }
}

export default Header;
