import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import Isvg from 'react-inlinesvg';

class TopBarNotificationIcon extends React.Component {
  static propTypes = {
    count: PropTypes.oneOfType([
      PropTypes.number,
      PropTypes.string
    ]),
    icon:      PropTypes.string,
    svg:       PropTypes.string,
    elementId: PropTypes.string,
    onClick:   PropTypes.func
  };
  static defaultProps = {
    onClick() {}
  };

  getIcon = () => {
    if (this.props.icon) {
      return <i className={classNames('icon', 'pointer', this.props.icon)} />;
    }
    if (this.props.svg) {
      return (<Isvg src={this.props.svg} />);
    }
    return null;
  };

  renderCount = () => {
    const { count } = this.props;
    if (parseInt(count, 10) > 0) {
      return <div className="ui knuckles label">{count}</div>;
    }
    return null;
  };

  render() {
    const { elementId, onClick, count } = this.props;
    return (<div id={elementId} className={classNames({ active: count > 0 })} onClick={onClick}>
      {this.getIcon()}
      {this.renderCount()}
    </div>);
  }
}
export default TopBarNotificationIcon;
