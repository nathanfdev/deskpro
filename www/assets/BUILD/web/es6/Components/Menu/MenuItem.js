import React, { PropTypes } from 'react';
import classNames from 'classnames';

class MenuItem extends React.Component {
  static propTypes = {
    label: PropTypes.string,
    icon: PropTypes.string,
    subContent: PropTypes.object,
    onClick: PropTypes.func
  };
  static defaultProps = {
    onClick: function() {}
  };

  handleClick(e) {
    this.props.onClick(e);
  }

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
    const {label, subContent, children} = this.props;
    return <a
      className={classNames('ui', 'item', { dropdown: !!subContent })}
      onClick={this.handleClick.bind(this)}
    >
      {this.getIcon()}
      {label}
      {children}

      {this.getSubContent()}
      </a>
  }
}
export default MenuItem;