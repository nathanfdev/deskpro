import React, { PropTypes } from 'react';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { MenuItem } from 'DeskPRO/Component/Semantic/Menu';

class User extends React.Component {
  static propTypes = {
    src: PropTypes.string
  };

  getPopupContent() {
    return (<div id="user-menu">
      <div className="header">{agentPhrases.get('agent.general.your_profile')}</div>
      <div className="description">
        <div className="ui vertical menu">
          <MenuItem onClick={this.clickSettings}>
            <i className="icon setting" /> {agentPhrases.get('agent.chrome.link_preferences')}</MenuItem>
          <MenuItem onClick={this.clickHelp}>
            <i className="icon help circle" /> {agentPhrases.get('agent.chrome.link_help')}</MenuItem>
          <MenuItem onClick={this.clickLogout}>
            <i className="icon reply" /> {agentPhrases.get('agent.chrome.link_logout')}</MenuItem>
        </div>
      </div>
    </div>);
  }

  closePopup = () => {
    this.userPopup.closePopup();
  };

  clickHelp = () => {
    const wrap = window.$('#dp_header_help');
    wrap.addClass('active');

    const closeFn = function closeFn() {
      wrap.removeClass('active');
    };

    if (!wrap.data('has-init')) {
      wrap.find('.btn-menu').on('click', (ev) => {
        window.Orb.cancelEvent(ev);
        window.Orb.shimClickCallbackPop();
      });
    }

    window.Orb.shimClickCallback(closeFn, 'zindex-chrome0');
    this.closePopup();
  };

  clickLogout = () => {
    window.location = `${window.DESKPRO_AGENT_LOGOUT}?to=agent`;
    this.closePopup();
  };

  clickSettings = () => {
    window.$('#settingswin').trigger('dp_open');
    this.closePopup();
  };

  togglePopup = () => {
    this.userPopup.togglePopup();
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
          ref={(c) => { this.userPopup = c; }}
          className="user_popup"
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
