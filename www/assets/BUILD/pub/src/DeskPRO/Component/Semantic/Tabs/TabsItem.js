import React, { PropTypes } from 'react';
import classNames from 'classnames';

class TabsItem extends React.Component {

  static propTypes = {
    tabId:   PropTypes.string.isRequired,
    active:  PropTypes.bool.isRequired,
    content: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.node
    ]).isRequired,
    classes: PropTypes.arrayOf(PropTypes.string)
  };


  render() {
    const { content, classes, active } = this.props;
    return (
      <div className={classNames('ui', 'bottom', 'attached', 'tab', 'segment', { active }, classes)}>
        {content}
      </div>
    );
  }
}
export default TabsItem;
