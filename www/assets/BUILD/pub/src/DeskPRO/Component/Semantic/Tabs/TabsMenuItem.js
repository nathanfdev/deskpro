import React, { PropTypes } from 'react';
import classNames from 'classnames';

class TabsMenuItem extends React.Component {

  static propTypes = {
    tabId:   PropTypes.string.isRequired,
    active:  PropTypes.bool.isRequired,
    onClick: PropTypes.func.isRequired,
    title:   PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.object
    ]).isRequired,
    classes: PropTypes.arrayOf(PropTypes.string).isRequired
  };

  render() {
    const { onClick, active, title, classes } = this.props;

    return (
      <a
        className={classNames('item', { active }, classes)}
        onClick={onClick}
      >
        {title}
      </a>
    );
  }
}
export default TabsMenuItem;
