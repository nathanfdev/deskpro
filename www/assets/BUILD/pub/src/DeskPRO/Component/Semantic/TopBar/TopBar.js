import PropTypes from 'prop-types';
import React from 'react';

class TopBar extends React.Component {
  static propTypes = {
    children: PropTypes.node
  };

  render() {
    return (<div className="ui menu topbar">
      {this.props.children}
    </div>);
  }
}
export default TopBar;
