import PropTypes from 'prop-types';
import React from 'react';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import UserProfile from './UserProfile';

class User extends React.Component {

  static propTypes = {
    imageUrl:   PropTypes.string,
    defaultUrl: PropTypes.string
  };

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
    const { imageUrl, defaultUrl } = this.props;

    return (
      <div className="user" onClick={this.togglePopup} title={agentPhrases.get('agent.chrome.user_tooltip')}>
        <PopUp
          positionMy="right top"
          positionAt="right bottom"
          elementId="user-menu-popup"
          zIndex={99999}
          content={(
            <UserProfile
              onClickPreferences={this.clickSettings}
              onClickHelp={this.clickHelp}
              onClickLogout={this.clickLogout}
            />
          )}
          ref={(c) => { this.userPopup = c; }}
          className="user_popup"
          autoOpen={false}
        >
          <img className="ui circular image" src={defaultUrl} alt="agent" />
          {imageUrl && <img className="ui circular image" src={imageUrl} style={{ position: 'absolute', left: 0, top: 3 }} alt="agent" />}
          <i className="dropdown icon" />
        </PopUp>
      </div>
    );
  }
}

export default User;
