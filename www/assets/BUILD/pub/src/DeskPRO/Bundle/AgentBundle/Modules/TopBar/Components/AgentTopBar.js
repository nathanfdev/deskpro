import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import Isvg from 'react-inlinesvg';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { Container } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/ChatWindow';
import * as chatsActions from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Actions/chatsActions';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';
import { TopBar, TopBarItem, TopBarRightMenu, TopBarNotificationIcon } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { loadFromApi, isLoadedCollectionSelectorFactory, collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { IMOverlay, IMButton, TopBarRecentImList } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/TopBar';
import AddButton from './AddButton';
import Chat from './Chat';
import User from './User';
import VoiceMenu from '../../Voice/Components/VoiceMenu/VoiceMenuContainer';
import { isVoiceEnabledSelector } from '../../Voice/Selectors/client';
import { onlineUserChatAgentsSelector, userChatEnabledSelector } from '../../Agent/Selectors/agents';
import { toggleUserChat } from '../../Agent/Actions/agentActions';

// import { IMContainer } from '../IM/Components/IMContainer';
// import { HeaderWidget } from '../IM/Components/HeaderWidget';

@connect(state => ({
  agents:           collectionSelectorFactory('Person', 'agents')(state),
  chatDepartments:  collectionSelectorFactory('Department', 'all_tickets')(state),
  myDepartments:    collectionSelectorFactory('Department', 'my_tickets')(state),
  recentChats:      collectionSelectorFactory('AgentChat', 'recent')(state),
  recentLoaded:     isLoadedCollectionSelectorFactory('AgentChat', 'recent')(state),
  teams:            collectionSelectorFactory('AgentTeam', 'my')(state),
  me:               meSelector(state),
  current:          state.IM.chats.get('current'),
  chating:          state.IM.chats.get('chating'),
  overlayShown:     state.IM.chats.get('overlayShown'),
  messages:         state.IM.messages,
  loadingMessages:  state.IM.messages.get('loadingMessages'),
  updatingMessages: state.IM.messages.get('updatingMessages')
  voiceEnabled:    isVoiceEnabledSelector(state),
  userChatEnabled: userChatEnabledSelector(state),
  onlineAgents:    onlineUserChatAgentsSelector(state),
}))
export class AgentTopBarContainer extends SeparateComponent {

  static propTypes = {
    agents:           PropTypes.object.isRequired,
    chatDepartments:  PropTypes.object.isRequired,
    myDepartments:    PropTypes.object.isRequired,
    teams:            PropTypes.object.isRequired,
    recentChats:      PropTypes.object.isRequired,
    recentLoaded:     PropTypes.bool.isRequired,
    loadingMessages:  PropTypes.bool.isRequired,
    updatingMessages: PropTypes.bool.isRequired,
    messages:         PropTypes.object,
    dispatch:         PropTypes.func.isRequired,
    me:               PropTypes.object,
    current:          PropTypes.object,
    chating:          PropTypes.bool.isRequired,
    overlayShown:     PropTypes.bool.isRequired
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
    this.toggleImOverlay = this.toggleImOverlay.bind(this);
    this.recentClick = this.recentClick.bind(this);
    this.participantClick = this.participantClick.bind(this);
    this.chatClickOut = this.chatClickOut.bind(this);
  }

  componentWillMount() {
    this.props.dispatch(loadFromApi(
      'AgentChat',
      'DP_API/agent_chats?order_by=date_last_message&order_dir=desc&count=5',
      'recent'
    ));
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

  recentClick(chatId) {
    this.props.dispatch(chatsActions.startChat(null, null, chatId));
  }

  participantClick(id, type) {
    this.props.dispatch(chatsActions.startChat(id, type));
  }

  chatClickOut(chatId) {
    this.props.dispatch(chatsActions.closeChat(chatId));
  }

  componentWillMount = () => {
    const oldNotifIcon = document.getElementById('notifs_counts');
    if (oldNotifIcon.innerHTML > 0) {
      this.setState({
        notificationCount: parseInt(oldNotifIcon.innerHTML, 10)
      });
    }
  };

  onSearchFocus = () => {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
    if (this.agentTopBar.searchBox.textInput.value) {
      window.$('.dp-omnibox-results').show();
    }
  };

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

  static onClearSearchInput() {
    window.$('.dp-omnibox-results').hide();
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

  onToggleChat = (enabled) => {
    this.props.dispatch(toggleUserChat(enabled));
  };

  toggleImOverlay() {
    this.props.dispatch(chatsActions.toggleOverlay());
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
      notificationCount: this.state.notificationCount,
      toggleImOverlay:   this.toggleImOverlay,
      chatClickOut:      this.chatClickOut,
      recentClick:       this.recentClick,
      participantClick:  this.recentClick,
      onClearSearchInput: AgentTopBarContainer.onClearSearchInput,
      onToggleChat:       this.onToggleChat
    };
    return <AgentTopBar {...props} ref={(c) => { this.agentTopBar = c; }} />;
  }
}
export class AgentTopBar extends React.Component {

  static propTypes = {
    agents:             PropTypes.object.isRequired,
    chatDepartments:    PropTypes.object.isRequired,
    myDepartments:      PropTypes.object.isRequired,
    teams:              PropTypes.object.isRequired,
    me:                 PropTypes.object.isRequired,
    recentChats:        PropTypes.object.isRequired,
    recentLoaded:       PropTypes.bool.isRequired,
    TopBar:             PropTypes.object,
    notificationCount:  PropTypes.number,
    dispatch:           PropTypes.func.isRequired,
    updateVolume:       PropTypes.func,
    onSearch:           PropTypes.func,
    onSearchFocus:      PropTypes.func,
    onSearchBlur:       PropTypes.func,
    onRecent:           PropTypes.func,
    onNotification:     PropTypes.func,
    closeIframes:       PropTypes.func,
    toggleImOverlay:    PropTypes.func,
    chatClickOut:       PropTypes.func,
    recentClick:        PropTypes.func,
    participantClick:   PropTypes.func,
    messages:           PropTypes.object,
    current:            PropTypes.number,
    chating:            PropTypes.bool.isRequired,
    overlayShown:       PropTypes.bool.isRequired,
    onClearSearchInput: PropTypes.func,
    toggleViewMode:     PropTypes.func,
    voiceEnabled:       PropTypes.bool,
    userChatEnabled:    PropTypes.bool,
    onlineAgents:       PropTypes.object,
    onToggleChat:       PropTypes.func
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
      return img.replace(/\{\{IMG_SIZE}}/, 56);
    }

    return '';
  };

  render() {
    const {current, chating, recentClick, messages, chatClickOut, participantClick, dispatch } = this.props;
    const { agents, chatDepartments, notificationCount, onlineAgents, userChatEnabled, voiceEnabled } = this.props;
    const { onSearch, onSearchFocus, onSearchBlur, onClearSearchInput, onRecent, onNotification, onToggleChat, toggleImOverlay } = this.props;
    const { closeIframes, toggleViewMode, me, myDepartments, teams, recentChats, recentLoaded, overlayShown } = this.props;

    return (<TopBar>
      <TopBarItem className="search-box legacy-omnibox">
        <SearchBox
          onUserInput={onSearch}
          onFocus={onSearchFocus}
          onBlur={onSearchBlur}
          onClearInput={onClearSearchInput}
          placeholder={`${agentPhrases.get('agent.chrome.nav_search')} ...`}
          ref={(c) => { this.searchBox = c; }}
        />
      </TopBarItem>
      <TopBarItem
        className="legacy-omnibox recent"
        onClick={onRecent}
        title={agentPhrases.get('agent.chrome.recent_tooltip')}
      >
        <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/recent.svg`} />
      </TopBarItem>
      <TopBarItem childrenWrapper="im-list">
        { recentLoaded
          ? <TopBarRecentImList
            me={me}
            agents={agents}
            departments={myDepartments}
            teams={teams}
            chats={recentChats}
            onRecentClick={recentClick}
          >
            <IMOverlay
              me={me}
              agents={agents}
              departments={myDepartments}
              teams={teams}
              onRecentClick={recentClick}
              onParticipantClick={participantClick}
              createNewGroup={() => console.log('create new group click')}
              isOpen={overlayShown}
              notifications={Immutable.fromJS({})}
              chats={recentChats}
              toggleOverlay={toggleImOverlay}
            >
              <IMButton />
            </IMOverlay>
            {current.get('id') ? <Container
              isOpen={chating}
              agents={agents}
              departments={myDepartments}
              teams={teams}
              me={me}
              messages={messages}
              current={current}
              onChange={() => console.log('change')}
              onAttach={() => console.log('attach')}
              clickOut={chatClickOut}
              dispatch={dispatch}
            /> : null }

          </TopBarRecentImList>
          : null
        }
      </TopBarItem>
      <AddButton closeIframes={closeIframes} />
      <TopBarRightMenu>
        <TopBarItem
          className="views"
          onClick={toggleViewMode}
          title={agentPhrases.get('agent.chrome.view_tooltip')}
        >
          <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/views.svg`} />
        </TopBarItem>
        <TopBarItem
          className="legacy-omnibox notifications"
          onClick={onNotification}
          title={agentPhrases.get('agent.chrome.notification_tooltip')}
        >
          <TopBarNotificationIcon
            elementId="notifications"
            svg={notificationsSvg}
            count={notificationCount}
          />
        </TopBarItem>
        <TopBarItem>
          {window.DP_HAS_VOICE && voiceEnabled && <VoiceMenu />}
          <User src={this.getUserPicture()} />
          <Chat
            activeChat={userChatEnabled}
            onlineAgents={onlineAgents}
            agents={agents.toArray()}
            chatDepartments={chatDepartments.toArray()}
            updateVolume={this.onChatVolumeUpdate}
            volume={8}
            onToggleChat={onToggleChat}
          />
        </TopBarItem>
      </TopBarRightMenu>
    </TopBar>
    );
  }
}

export default AgentTopBar;
