import React, { PropTypes } from 'react';

class Header extends React.Component {
  static propTypes = {
    size:    PropTypes.number.isRequired,
    content: PropTypes.string.isRequired
  };

  render() {
    const { size, content } = this.props;
    const element = `h${size}`;
    return React.createElement(element, { className: 'ui header' }, content);
  }
}

export default Header;
