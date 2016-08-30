import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Isvg from 'react-inlinesvg';
import classNames from 'classnames';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';
import { TopBar, TopBarItem, TopBarRightMenu, TopBarNotificationIcon } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import AddButton from './AddButton';
import Chat from './Chat';
import User from './User';
import { resumeOnboarding } from '../../Onboarding/Actions/onboardingActions';
// import { IMContainer } from '../IM/Components/IMContainer';
// import { HeaderWidget } from '../IM/Components/HeaderWidget';

@connect(state => ({
  agents:          collectionSelectorFactory('Person', 'agents')(state),
  chatDepartments: collectionSelectorFactory('Department', 'all_tickets')(state),
  me:              meSelector(state),
  logoCallback:    state.Onboarding.onboarding.get('logoCallback'),
  logoActive:      state.Onboarding.onboarding.get('logoActive'),
}))
class AgentTopBar extends SeparateComponent {

  static propTypes = {
    agents:          PropTypes.object.isRequired,
    chatDepartments: PropTypes.object.isRequired,
    me:              PropTypes.object,
    logoCallback:    PropTypes.func,
    logoActive:      PropTypes.bool,
    TopBar:          PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      notificationCount: 0
    };
    window.document.addEventListener('dpUpdateNotifCount', (e) => {
      this.setState({
        notificationCount: e.detail.count
      });
    });
  }

  static getType() {
    return 'AgentTopBar';
  }

  onChatVolumeUpdate(newVal) {
    const volume = newVal / 10;
    window.DeskPRO_Window.volume = volume;

    window.$('audio').each(function changeVolume() {
      this.volume = volume;
    });
  }

  onSearch(searchQuery) {
    const angularOmnibox = window.angular.element('.dp-omnibox').scope();
    if (angularOmnibox && searchQuery) {
      angularOmnibox.searchQuery = searchQuery;
      angularOmnibox.touchSearch();
      window.$('.dp-omnibox-results').show();
    }
  }

  onSearchFocus() {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
  }

  onSearchBlur() {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
  }

  onRecent() {
    const angularOmnibox = window.angular.element('.dp-omnibox').scope();
    if (angularOmnibox) {
      angularOmnibox.toggleMode('recent');
      const event = new Event('dpPopupOpen');
      window.document.dispatchEvent(event);
    }
  }

  onNotification() {
    const angularOmnibox = window.angular.element('.dp-omnibox').scope();
    if (angularOmnibox) {
      angularOmnibox.toggleMode('notif');
      const event = new Event('dpPopupOpen');
      window.document.dispatchEvent(event);
    }
  }

  getUserPicture = () => {
    const { me } = this.props;
    if (!me) {
      return '';
    }

    let img = me.getIn(['avatar', 'default_url_pattern']);
    if (me.getIn(['avatar', 'url_pattern'])) {
      img = me.getIn(['avatar', 'url_pattern']);
    }
    if (img) {
      return img.replace(/\{\{IMG_SIZE}}/, 32);
    }

    return '';
  };

  closeIframes() {
    for (const key of Object.keys(window.DP_FRAME_OVERLAYS)) {
      const iframe = window.DP_FRAME_OVERLAYS[key];
      if (iframe.opened) {
        iframe.close();
      }
    }
  }

  toggleViewMode() {
    if (window.DeskPRO_Window.paneVis.tabs && window.DeskPRO_Window.paneVis.list) {
      window.DeskPRO_Window.$scope.oneColumnView();
    } else {
      window.DeskPRO_Window.$scope.twoColumnsView();
    }
  }

  clickLogo = () => {
    if (this.props.logoActive) {
      this.props.logoCallback();
      this.props.dispatch(resumeOnboarding());
    } else {
      this.openDeskPro();
    }
  };

  openDeskPro() {
    window.open('http://deskpro.com', '_blank');
  }

  render() {
    const { notificationCount } = this.state;
    const { agents, chatDepartments, logoActive } = this.props;
    const svgSrc = window.DESKPRO_APP_ASSETS_URL.replace(/\/$/, '');

    return (<TopBar>
      <div className={classNames('logo', { active: logoActive })} onClick={this.clickLogo} />
      <TopBarItem classes={['search-box legacy-omnibox']}>
        <SearchBox
          onUserInput={this.onSearch}
          onFocus={this.onSearchFocus}
          onBlur={this.onSearchBlur}
          placeholder={`${agentPhrases.get('agent.chrome.nav_search')} ...`}
        />
      </TopBarItem>
      <TopBarItem classes={['legacy-omnibox recent']}>
        <Isvg src={`${svgSrc}/../src/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/recent.svg`}  onClick={this.onRecent}/>
      </TopBarItem>
      {/* <TopBarItem classes={['z-index-stub']}>*/}
        {/* <HeaderWidget />*/}
        {/* <IMContainer />*/}
      {/* </TopBarItem>*/}

      <AddButton closeIframes={this.closeIframes} />
      <TopBarRightMenu>
        <TopBarItem classes={['views']} onClick={this.toggleViewMode}>
          <Isvg src={`${svgSrc}/../src/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/views.svg`} />
        </TopBarItem>
        <TopBarItem classes={['legacy-omnibox notifications']} onClick={this.onNotification}>
          <TopBarNotificationIcon
            elementId="notifications"
            svg="notifications.svg"
            count={notificationCount}
          />
        </TopBarItem>
        <TopBarItem>
          <User src={this.getUserPicture()} />
          <Chat
            agents={agents.toArray()}
            chatDepartments={chatDepartments.toArray()}
            updateVolume={this.onChatVolumeUpdate} volume={8}
          />
        </TopBarItem>
      </TopBarRightMenu>
    </TopBar>);
  }
}
export default AgentTopBar;
