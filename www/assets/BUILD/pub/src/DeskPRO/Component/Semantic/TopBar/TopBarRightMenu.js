import PropTypes from 'prop-types';
import React from 'react';

class TopBarRightMenu extends React.Component {
  static propTypes = {
    children: PropTypes.node
  };

  render() {
    return (<div className="right menu">
      {this.props.children}
    </div>);
  }
}
export default TopBarRightMenu;
