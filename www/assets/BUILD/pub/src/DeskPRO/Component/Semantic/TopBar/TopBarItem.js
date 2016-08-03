import React, { PropTypes } from 'react';

class TopBarItem extends React.Component {
  static propTypes = {
    onClick:  PropTypes.func,
    children: PropTypes.node
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
    return (<div className="item" onClick={this.handleClick}>
      <div>
        {this.props.children}
      </div>
    </div>);
  }
}
export default TopBarItem;
