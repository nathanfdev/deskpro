import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class TabsMenuItem extends React.Component {

  static propTypes = {
    active:  PropTypes.bool.isRequired,
    onClick: PropTypes.func.isRequired,
    title:   PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.object
    ]).isRequired,
    className: PropTypes.string
  };

  static defaultProps = {
    className: ''
  };

  render() {
    const { onClick, active, title, className } = this.props;

    return (
      <a
        className={classNames('item', { active }, className)}
        onClick={onClick}
      >
        {title}
      </a>
    );
  }
}
export default TabsMenuItem;
