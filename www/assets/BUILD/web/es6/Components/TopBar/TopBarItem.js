import React, { PropTypes } from 'react';

class TopBarItem extends React.Component {
  static propTypes = {
    onClick: PropTypes.func
  };
  static defaultProps = {
    onClick: function() {}
  };

  handleClick(e) {
    this.props.onClick(e);
  }

  render() {
    return <div className="item" onClick={this.handleClick.bind(this)}>
      <div>
        {this.props.children}
      </div>
    </div>
  }
}
export default TopBarItem;