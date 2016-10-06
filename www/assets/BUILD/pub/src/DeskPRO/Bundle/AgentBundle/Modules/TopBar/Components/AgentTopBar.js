import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Isvg from 'react-inlinesvg';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';
import { TopBar, TopBarItem, TopBarRightMenu, TopBarNotificationIcon } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import AddButton from './AddButton';
import Chat from './Chat';
import User from './User';
// import { IMContainer } from '../IM/Components/IMContainer';
// import { HeaderWidget } from '../IM/Components/HeaderWidget';

@connect(state => ({
  agents:          collectionSelectorFactory('Person', 'agents')(state),
  chatDepartments: collectionSelectorFactory('Department', 'all_tickets')(state),
  me:              meSelector(state),
}))
export class AgentTopBarContainer extends SeparateComponent {
  static propTypes = {
    agents:          PropTypes.object.isRequired,
    chatDepartments: PropTypes.object.isRequired,
    me:              PropTypes.object
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

  static updateVolume(volume) {
    window.DeskPRO_Window.volume = volume;

    window.$('audio').each(function changeVolume() {
      this.volume = volume;
    });
  }

  static onSearch(searchQuery) {
    const angularOmnibox = window.angular.element('.dp-omnibox').scope();
    if (angularOmnibox && searchQuery) {
      angularOmnibox.searchQuery = searchQuery;
      angularOmnibox.touchSearch();
      window.$('.dp-omnibox-results').show();
    }
  }

  onSearchFocus = () => {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
    if (this.agentTopBar.searchBox.textInput.value) {
      window.$('.dp-omnibox-results').show();
    }
  }

  static onSearchBlur() {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
  }

  static onRecent() {
    const angularOmnibox = window.angular.element('.dp-omnibox').scope();
    if (angularOmnibox) {
      angularOmnibox.toggleMode('recent');
      const event = new Event('dpPopupOpen');
      window.document.dispatchEvent(event);
    }
  }

  static onNotification() {
    const angularOmnibox = window.angular.element('.dp-omnibox').scope();
    if (angularOmnibox) {
      angularOmnibox.toggleMode('notif');
      const event = new Event('dpPopupOpen');
      window.document.dispatchEvent(event);
    }
  }

  static toggleViewMode() {
    if (window.DeskPRO_Window.paneVis.tabs && window.DeskPRO_Window.paneVis.list) {
      window.DeskPRO_Window.$scope.oneColumnView();
    } else {
      window.DeskPRO_Window.$scope.twoColumnsView();
    }
  }

  static closeIframes() {
    for (const key of Object.keys(window.DP_FRAME_OVERLAYS)) {
      const iframe = window.DP_FRAME_OVERLAYS[key];
      if (iframe.opened) {
        iframe.close();
      }
    }
  }

  render() {
    const props = { ...this.props,
      updateVolume:      AgentTopBarContainer.updateVolume,
      onSearch:          AgentTopBarContainer.onSearch,
      onSearchFocus:     this.onSearchFocus,
      onSearchBlur:      AgentTopBarContainer.onSearchBlur,
      toggleViewMode:    AgentTopBarContainer.toggleViewMode,
      onRecent:          AgentTopBarContainer.onRecent,
      onNotification:    AgentTopBarContainer.onNotification,
      closeIframes:      AgentTopBarContainer.closeIframes,
      notificationCount: this.state.notificationCount
    };
    return <AgentTopBar {...props} ref={(c) => { this.agentTopBar = c; }} />;
  }
}
export class AgentTopBar extends React.Component {

  static propTypes = {
    agents:            PropTypes.object.isRequired,
    chatDepartments:   PropTypes.object.isRequired,
    me:                PropTypes.object,
    notificationCount: PropTypes.number,
    updateVolume:      PropTypes.func,
    onSearch:          PropTypes.func,
    onSearchFocus:     PropTypes.func,
    onSearchBlur:      PropTypes.func,
    onRecent:          PropTypes.func,
    onNotification:    PropTypes.func,
    closeIframes:      PropTypes.func,
    toggleViewMode:    PropTypes.func,
  };

  onChatVolumeUpdate = (newVal) => {
    this.props.updateVolume(newVal / 10);
  };

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

  render() {
    const { agents, chatDepartments, notificationCount } = this.props;

    return (<TopBar>
      <TopBarItem className="search-box legacy-omnibox">
        <SearchBox
          onUserInput={this.props.onSearch}
          onFocus={this.props.onSearchFocus}
          onBlur={this.props.onSearchBlur}
          placeholder={`${agentPhrases.get('agent.chrome.nav_search')} ...`}
          ref={(c) => { this.searchBox = c; }}
        />
      </TopBarItem>
      <TopBarItem className="legacy-omnibox recent" onClick={this.props.onRecent}>
        <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/recent.svg`} />
      </TopBarItem>
      {/* <TopBarItem className='z-index-stub'>*/}
      {/* <HeaderWidget />*/}
      {/* <IMContainer />*/}
      {/* </TopBarItem>*/}

      <AddButton closeIframes={this.props.closeIframes} />
      <TopBarRightMenu>
        <TopBarItem className="views" onClick={this.props.toggleViewMode}>
          <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/views.svg`} />
        </TopBarItem>
        <TopBarItem className="legacy-omnibox notifications" onClick={this.props.onNotification}>
          <TopBarNotificationIcon
            elementId="notifications"
            svg={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/notifications.svg`}
            count={notificationCount}
          />
        </TopBarItem>
        <TopBarItem>
          <User src={this.getUserPicture()} />
          <Chat
            agents={agents.toArray()}
            chatDepartments={chatDepartments.toArray()}
            updateVolume={this.onChatVolumeUpdate}
            volume={8}
          />
        </TopBarItem>
      </TopBarRightMenu>
    </TopBar>);
  }
}
export default AgentTopBar;
