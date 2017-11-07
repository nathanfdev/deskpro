import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class TabsItem extends React.Component {

  static propTypes = {
    active:  PropTypes.bool.isRequired,
    content: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.node
    ]).isRequired,
    className: PropTypes.string
  };


  render() {
    const { content, className, active } = this.props;
    return (
      <div className={classNames('ui', 'bottom', 'attached', 'tab', 'segment', { active }, className)}>
        {content}
      </div>
    );
  }
}
export default TabsItem;
