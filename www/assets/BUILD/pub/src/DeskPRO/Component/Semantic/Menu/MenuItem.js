import React, { PropTypes } from 'react';
import classNames from 'classnames';

class MenuItem extends React.Component {
  static propTypes = {
    label:       PropTypes.string,
    icon:        PropTypes.string,
    subContent:  PropTypes.object,
    onClick:     PropTypes.func,
    onMouseOver: PropTypes.func,
    onMouseOut:  PropTypes.func,
    children:    PropTypes.node,
    className:   PropTypes.string
  };
  static defaultProps = {
    onClick() {},
    onMouseOver() {},
    onMouseOut() {}
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

  handleMouseOver = (e) => {
    this.props.onMouseOver(e);
  };

  handleMouseOut = (e) => {
    this.props.onMouseOut(e);
  };

  render() {
    const { label, subContent, children, className } = this.props;
    return (<a
      className={classNames('ui', 'item', { dropdown: !!subContent }, className)}
      onClick={this.handleClick}
      onMouseOver={this.handleMouseOver}
      onMouseOut={this.handleMouseOut}
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
