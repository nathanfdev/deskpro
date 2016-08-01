import React from 'react';

class TopBarRightMenu extends React.Component {
  render() {
    return <div className="right menu">
      {this.props.children}
    </div>
  }
}
export default TopBarRightMenu;