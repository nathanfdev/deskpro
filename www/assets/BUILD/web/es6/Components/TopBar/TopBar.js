import React from 'react';

class TopBar extends React.Component {
  render() {
    return <div className="ui menu topbar">
      {this.props.children}
      </div>
  }
}
export default TopBar;