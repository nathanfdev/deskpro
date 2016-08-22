import React, { PropTypes } from 'react';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { MenuItem } from 'DeskPRO/Component/Semantic/Menu';

class User extends React.Component {
  static propTypes = {
    src: PropTypes.string
  };

  static clickLogout() {
    window.location = `${window.DESKPRO_AGENT_LOGOUT}?to=agent`;
  }

  getPopupContent() {
    return (<div id="user-menu">
      <div className="header">Your Profile</div>
      <div className="description">
        <div className="ui vertical menu">
          <MenuItem onClick={this.clickSettings}><i className="icon setting" /> Preferences</MenuItem>
          <MenuItem onClick={this.clickHelp}><i className="icon help circle" /> Help</MenuItem>
          <MenuItem onClick={this.clickLogout}><i className="icon reply" /> Log out</MenuItem>
        </div>
      </div>
    </div>);
  }

  closePopup = () => {
    this.refs.userPopup.closePopup();
  };

  clickHelp = () => {
    const wrap = window.$('#dp_header_help');
    wrap.addClass('active');

    const closeFn = function closeFn() {
      wrap.removeClass('active');
    };

    if (!wrap.data('has-init')) {
      wrap.find('.btn-menu').on('click', ev => {
        window.Orb.cancelEvent(ev);
        window.Orb.shimClickCallbackPop();
      });
    }

    window.Orb.shimClickCallback(closeFn, 'zindex-chrome0');
    this.closePopup();
  };

  clickSettings = () => {
    window.$('#settingswin').trigger('dp_open');
    this.closePopup();
  };

  togglePopup = () => {
    this.refs.userPopup.togglePopup();
  };

  render() {
    const { src } = this.props;
    return (
      <div className="user" onClick={this.togglePopup}>
        <PopUp
          positionMy="right top"
          positionAt="right bottom"
          id={1}
          elementId="user-menu-popup"
          zIndex={99999}
          content={this.getPopupContent()}
          ref="userPopup"
          classes={['user_popup']}
          autoOpen={false}
        >
          <img className="ui circular image" src={src} alt="agent" />
          <i className="dropdown icon" />
        </PopUp>
      </div>
    );
  }
}
export default User;
