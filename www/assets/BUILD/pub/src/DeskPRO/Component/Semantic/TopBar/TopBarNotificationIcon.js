import React, { PropTypes } from 'react';
import classNames from 'classnames';

class TopBarNotificationIcon extends React.Component {
  static propTypes = {
    count: PropTypes.oneOfType([
      PropTypes.number,
      PropTypes.string
    ]),
    icon: PropTypes.string
  };

  render() {
    const { icon, count } = this.props;
    return (<div>
      <i className={classNames('icon', icon)} />
      <div className="ui knuckles label">{count}</div>
    </div>);
  }
}
export default TopBarNotificationIcon;
