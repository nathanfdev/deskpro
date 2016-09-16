import React, { PropTypes } from 'react';
import classNames from 'classnames';

class MenuItem extends React.Component {
  static propTypes = {
    label:      PropTypes.string,
    icon:       PropTypes.string,
    subContent: PropTypes.object,
    onClick:    PropTypes.func,
    children:   PropTypes.node,
    classes:    PropTypes.string
  };
  static defaultProps = {
    onClick() {}
  };

  getIcon() {
    const { icon } = this.props;
    if (icon) {
      return <i className={classNames('icon', icon)} />;
    }
    return null;
  }

  getSubContent() {
    const { subContent } = this.props;
    if (subContent) {
      return <i className="dropdown icon" />;
    }
    return null;
  }

  handleClick = (e) => {
    this.props.onClick(e);
  };

  render() {
    const { label, subContent, children, classes } = this.props;
    return (<a
      className={classNames('ui', 'item', { dropdown: !!subContent }, classes)}
      onClick={this.handleClick}
      ref={(c) => { this.node = c; }}
    >
      {this.getIcon()}
      {label}
      {children}

      {this.getSubContent()}
    </a>);
  }
}
export default MenuItem;
