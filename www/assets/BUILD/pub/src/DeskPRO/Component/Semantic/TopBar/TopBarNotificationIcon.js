import React, { PropTypes } from 'react';
import classNames from 'classnames';

class TopBarNotificationIcon extends React.Component {
  static propTypes = {
    count: PropTypes.oneOfType([
      PropTypes.number,
      PropTypes.string
    ]),
    icon:      PropTypes.string,
    elementId: PropTypes.string,
    onClick:   PropTypes.func
  };
  static defaultProps = {
    onClick() {}
  };

  renderCount = () => {
    const { count } = this.props;
    if (parseInt(count, 10) > 0) {
      return <div className="ui knuckles label">{count}</div>;
    }
    return null;
  };

  render() {
    const { icon, elementId, onClick, count } = this.props;
    return (<div id={elementId} className={classNames({ active: count > 0 })}>
      <i className={classNames('icon', 'pointer', icon)} onClick={onClick} />
      {this.renderCount()}
    </div>);
  }
}
export default TopBarNotificationIcon;
