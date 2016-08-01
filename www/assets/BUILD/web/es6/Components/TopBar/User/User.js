import React, { PropTypes } from 'react';
import { PopUp } from 'Components/PopUp';
import { Menu, MenuItem } from 'Components/Menu';

class User extends React.Component {
  static propTypes = {
    src: PropTypes.string
  };
  
  getPopupContent() {
    return <div id="user-menu">
      <div className="header">Your Profile</div>
      <div className="description">
        <div className="ui vertical menu">
          <MenuItem><i className="icon setting"/> Preferences</MenuItem>
          <MenuItem><i className="icon help circle"/> Help</MenuItem>
          <MenuItem><i className="icon reply"/> Log out</MenuItem>
        </div>
      </div>
    </div>
  }

  render() {
    const {src} = this.props;
    return <div className="user">
      <PopUp positionMy="right top"
             positionAt="right bottom"
             id={1}
             elementId="user-menu-popup"
             zIndex={99999}
             content={this.getPopupContent()}
      >
        <img className="ui circular image" src={src} />
        <i className="dropdown icon"/>
      </PopUp>
    </div>
  }
}
export default User;