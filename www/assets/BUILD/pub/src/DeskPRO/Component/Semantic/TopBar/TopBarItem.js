import React, { PropTypes } from 'react';
import classNames from 'classnames';

class TopBarItem extends React.Component {
  static propTypes = {
    onClick:   PropTypes.func,
    children:  PropTypes.node,
    className: PropTypes.string
  };
  static defaultProps = {
    onClick() {}
  };

  handleClick = (e) => {
    this.props.onClick(e);
  };

  render() {
    const { children, className } = this.props;
    return (<div className={classNames('item', className)} onClick={this.handleClick}>
      <div>
        {children}
      </div>
    </div>);
  }
}
export default TopBarItem;
