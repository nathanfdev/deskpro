import React, { PropTypes } from 'react';
import classNames from 'classnames';

class TopBarNotificationIcon extends React.Component {
  static propTypes = {
    count: PropTypes.oneOfType([
      PropTypes.number,
      PropTypes.string
    ]),
    icon:      PropTypes.string,
    elementId: PropTypes.string
  };

  render() {
    const { icon, count, elementId } = this.props;
    return (<div id={elementId}>
      <i className={classNames('icon', icon)} />
      <div className="ui knuckles label">{count}</div>
    </div>);
  }
}
export default TopBarNotificationIcon;
