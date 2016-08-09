import React, { PropTypes } from 'react';
import classNames from 'classnames';

class TopBarItem extends React.Component {
  static propTypes = {
    onClick:  PropTypes.func,
    children: PropTypes.node,
    classes:  PropTypes.array
  };
  static defaultProps = {
    onClick() {}
  };

  constructor() {
    super();
    this.handleClick = this.handleClick.bind(this);
  }

  handleClick(e) {
    this.props.onClick(e);
  }

  render() {
    const { children, classes } = this.props;
    return (<div className={classNames('item', classes)} onClick={this.handleClick}>
      <div>
        {children}
      </div>
    </div>);
  }
}
export default TopBarItem;
