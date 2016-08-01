import React, { PropTypes } from 'react';
import classNames from 'classnames';

class MenuItem extends React.Component {
  static propTypes = {
    label: PropTypes.string.isRequired,
    icon: PropTypes.string,
    subContent: PropTypes.object
  };

  getIcon() {
    const {icon} = this.props;
    if (icon) {
      return <i className={classNames('icon', icon)} />
    }
  }

  getSubContent() {
    const {subContent} = this.props;
    if (subContent) {
      return <i className="dropdown icon"/>;
    }
  }

  render() {
    const {label, subContent} = this.props;
    return <a className={classNames('ui', 'item', { dropdown: !!subContent })}>
      {this.getIcon()}
      {label}

      {this.getSubContent()}
      </a>
  }
}
export default MenuItem;