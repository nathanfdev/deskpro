import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Isvg from 'react-inlinesvg';
import uuid from 'node-uuid';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { Container } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/ChatWindow';
import * as chatsActions from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Actions/chatsActions';
import * as messagesActions from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Actions/messagesActions';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';
import { TopBar, TopBarItem, TopBarRightMenu, TopBarNotificationIcon } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { loadFromApi, isLoadedCollectionSelectorFactory, collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import notificationsSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/topbar/notifications.svg';
import { IMOverlay, IMButton, TopBarRecentImList, GroupAddDrawer } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/TopBar';
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
  agents:              collectionSelectorFactory('Person', 'agents')(state),
  chatDepartments:     collectionSelectorFactory('Department', 'all_tickets')(state),
  recentChats:         collectionSelectorFactory('AgentChat', 'recent')(state),
  groupChats:          collectionSelectorFactory('AgentChat', 'group')(state),
  myDepartments:       collectionSelectorFactory('Department', 'my_tickets')(state),
  teams:               collectionSelectorFactory('AgentTeam', 'all')(state),
  me:                  meSelector(state),
  counts:              state.IM.messages.get('counts'),
  current:             state.IM.chats.get('current'),
  editChat:            state.IM.chats.get('editChat'),
  chating:             state.IM.chats.get('chating'),
  overlayShown:        state.IM.chats.get('overlayShown'),
  groupCreation:       state.IM.chats.get('groupCreation'),
  checkedAgents:       state.IM.chats.get('checkedAgents'),
  messages:            state.IM.messages,
  loadingMessages:     state.IM.messages.get('loadingMessages'),
  updatingMessages:    state.IM.messages.get('updatingMessages'),
  recentLoaded:        isLoadedCollectionSelectorFactory('AgentChat', 'recent')(state),
  groupLoaded:         isLoadedCollectionSelectorFactory('AgentChat', 'group')(state),
  teamsLoaded:         isLoadedCollectionSelectorFactory('AgentTeam', 'my')(state),
  myDepartmentsLoaded: isLoadedCollectionSelectorFactory('Department', 'my_tickets')(state),
  agentsLoaded:        isLoadedCollectionSelectorFactory('Person', 'agents')(state),
  voiceEnabled:    isVoiceEnabledSelector(state),
  userChatEnabled: userChatEnabledSelector(state),
  onlineAgents:    onlineUserChatAgentsSelector(state),
}))
export class AgentTopBarContainer extends SeparateComponent {

  static propTypes = {
    me:                  PropTypes.object,
    dispatch:            PropTypes.func.isRequired,
    agents:              PropTypes.object.isRequired,
    chatDepartments:     PropTypes.object.isRequired,
    myDepartments:       PropTypes.object.isRequired,
    teams:               PropTypes.object.isRequired,
    recentChats:         PropTypes.object.isRequired,
    groupChats:          PropTypes.object.isRequired,
    messages:            PropTypes.object,
    counts:              PropTypes.object,
    current:             PropTypes.object,
    editChat:            PropTypes.object,
    checkedAgents:       PropTypes.object,
    recentLoaded:        PropTypes.bool.isRequired,
    groupLoaded:         PropTypes.bool.isRequired,
    loadingMessages:     PropTypes.bool.isRequired,
    updatingMessages:    PropTypes.bool.isRequired,
    chating:             PropTypes.bool.isRequired,
    overlayShown:        PropTypes.bool.isRequired,
    teamsLoaded:         PropTypes.bool.isRequired,
    myDepartmentsLoaded: PropTypes.bool.isRequired,
    agentsLoaded:        PropTypes.bool.isRequired,
    groupCreation:       PropTypes.bool.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      notificationCount: 0,
      searchQuery:       '',
      page:              0
    };
    window.document.addEventListener('dpUpdateNotifCount', (e) => {
      this.setState({
        notificationCount: e.detail.count
      });
    });
    this.toggleImOverlay  = this.toggleImOverlay.bind(this);
    this.recentClick      = this.recentClick.bind(this);
    this.participantClick = this.participantClick.bind(this);
    this.chatClickOut     = this.chatClickOut.bind(this);
    this.onSubmit         = this.onSubmit.bind(this);
    this.markNewMessages  = this.markNewMessages.bind(this);
    this.openGroupDrawer  = this.openGroupDrawer.bind(this);
    this.createGroup      = this.createGroup.bind(this);
    this.updateGroup      = this.updateGroup.bind(this);
    this.onScroll         = this.onScroll.bind(this);
    this.loadMessages     = this.loadMessages.bind(this);
    this.onChatSearch     = this.onChatSearch.bind(this);
  }

  componentWillMount() {
    this.props.dispatch(loadFromApi(
      'AgentChat',
      'DP_API/agent_chats?order_by=date_last_message&order_dir=desc&count=5',
      'recent'
    ));
    this.props.dispatch(loadFromApi(
      'AgentChat',
      'DP_API/agent_chats/groups',
      'group'
    ));

    this.refreshCounts();
  }

  refreshCounts() {
    if (this.props.recentLoaded) {
      this.props.dispatch(messagesActions.refreshCounts());
    } else {
      setTimeout(this.refreshCounts.bind(this), 2000);
    }
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
    this.props.dispatch(chatsActions.startChat(null, chatId));
  }

  participantClick(id, type) {
    this.props.dispatch(chatsActions.startChat({ id, type }));
  }

  chatClickOut(chatId) {
    this.props.dispatch(chatsActions.closeChat(chatId));
  }

  onSubmit(message) {
    if (message.replace(/^<p>|<\/p>$/g, '').trim()) {
      const { dispatch, current, me } = this.props;
      dispatch(messagesActions.addMessage(current.get('id'), message, uuid(), me));
    }
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

  openGroupDrawer(agentIds = [], chat = null) {
    this.props.dispatch(chatsActions.openGroupDrawer(agentIds, chat));
  }

  createGroup(agentIds, groupName) {
    this.props.dispatch(chatsActions.startChat({ id: agentIds, type: 'group', name: groupName }));
  }

  updateGroup(chatId, ids, name) {
    this.props.dispatch(chatsActions.updateChat(chatId, ids, name));
  }

  getPath = () => {
    let path;
    if (!this.state.searchQuery) {
      path = ['chatMessages', this.props.current.get('id')];
    } else {
      path = ['searchMessages', this.props.current.get('id')];
    }
    return path;
  };

  markNewMessages() {
    if (!this.props.updatingMessages) {
      const ids = [];
      const uuids = [];
      const { messages, dispatch } = this.props;

      const msg = messages.hasIn(this.getPath()) ? messages.getIn(this.getPath()).messages : [];
      msg.map((message) => {
        if (message.id && message.status < 2 && message.person !== this.props.me.get('id')) {
          ids.push(message.id);
          uuids.push(message.uuid);
        }
        return true;
      });
      if (ids.length > 0) {
        dispatch(messagesActions.markMessages(ids, uuids, this.props.current.get('id')));
      }
    }
  }

  onScroll(object) {
    const messages = this.props.messages.getIn(this.getPath());
    if (messages) {
      const page = messages.page;
      const maxPage = messages.pages;
      if (object.topPosition === 0 && !this.props.loadingMessages && page < maxPage) {
        this.loadMessages(page + 1);
      }
    }
  }

  onChatSearch(searchQuery) {
    let reload = false;
    if ((this.state.searchQuery !== searchQuery && searchQuery.length > 2) || searchQuery === '') {
      reload = true;
    }
    if (reload && !this.props.loadingMessages) {
      this.setState({ searchQuery, page: 1 }, () => { this.loadMessages(1); });
    }
  }

  loadMessages(page = 1) {
    this.props.dispatch(messagesActions.loadMessages(this.props.current.get('id'), this.state.searchQuery, page));
  }

  render() {
    const props = {
      ...this.props,
      updateVolume:       AgentTopBarContainer.updateVolume,
      onSearch:           AgentTopBarContainer.onSearch,
      onSearchFocus:      this.onSearchFocus,
      onSearchBlur:       AgentTopBarContainer.onSearchBlur,
      toggleViewMode:     AgentTopBarContainer.toggleViewMode,
      onRecent:           AgentTopBarContainer.onRecent,
      onNotification:     AgentTopBarContainer.onNotification,
      closeIframes:       AgentTopBarContainer.closeIframes,
      onClearSearchInput: AgentTopBarContainer.onClearSearchInput,
      notificationCount:  this.state.notificationCount,
      toggleImOverlay:    this.toggleImOverlay,
      chatClickOut:       this.chatClickOut,
      recentClick:        this.recentClick,
      participantClick:   this.participantClick,
      onSubmit:           this.onSubmit,
      markNewMessages:    this.markNewMessages,
      openGroupDrawer:    this.openGroupDrawer,
      createGroup:        this.createGroup,
      updateGroup:        this.updateGroup,
      onScroll:           this.onScroll,
      loadMessages:       this.loadMessages,
      onChatSearch:       this.onChatSearch,
      searchQuery:        this.state.searchQuery,
      shouldScrollBottom: this.state.shouldScrollBottom,
      onToggleChat:       this.onToggleChat
    };
    return <AgentTopBar {...props} ref={(c) => { this.agentTopBar = c; }} />;
  }
}
export class AgentTopBar extends React.Component {

  static propTypes = {
    agents:              PropTypes.object.isRequired,
    chatDepartments:     PropTypes.object.isRequired,
    myDepartments:       PropTypes.object.isRequired,
    teams:               PropTypes.object.isRequired,
    me:                  PropTypes.object.isRequired,
    recentChats:         PropTypes.object.isRequired,
    groupChats:          PropTypes.object.isRequired,
    notificationCount:   PropTypes.number,
    dispatch:            PropTypes.func.isRequired,
    updateVolume:        PropTypes.func,
    onSearch:            PropTypes.func,
    onSearchFocus:       PropTypes.func,
    onSearchBlur:        PropTypes.func,
    onRecent:            PropTypes.func,
    onNotification:      PropTypes.func,
    closeIframes:        PropTypes.func,
    toggleViewMode:      PropTypes.func,
    toggleImOverlay:     PropTypes.func,
    openGroupDrawer:     PropTypes.func,
    chatClickOut:        PropTypes.func,
    recentClick:         PropTypes.func,
    participantClick:    PropTypes.func,
    createGroup:         PropTypes.func,
    updateGroup:         PropTypes.func,
    onSubmit:            PropTypes.func,
    markNewMessages:     PropTypes.func,
    onScroll:            PropTypes.func,
    loadMessages:        PropTypes.func,
    onChatSearch:        PropTypes.func,
    messages:            PropTypes.object,
    counts:              PropTypes.object,
    current:             PropTypes.object,
    editChat:            PropTypes.object,
    checkedAgents:       PropTypes.object,
    searchQuery:         PropTypes.string,
    loadingMessages:     PropTypes.bool.isRequired,
    groupCreation:       PropTypes.bool.isRequired,
    chating:             PropTypes.bool.isRequired,
    overlayShown:        PropTypes.bool.isRequired,
    recentLoaded:        PropTypes.bool.isRequired,
    groupLoaded:         PropTypes.bool.isRequired,
    teamsLoaded:         PropTypes.bool.isRequired,
    myDepartmentsLoaded: PropTypes.bool.isRequired,
    agentsLoaded:        PropTypes.bool.isRequired,
    onClearSearchInput:  PropTypes.func,
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
    const { groupLoaded, groupCreation, teamsLoaded, myDepartmentsLoaded, agentsLoaded, loadingMessages } = this.props;
    const { current, chating, recentClick, messages, chatClickOut, participantClick, dispatch } = this.props;
    const { searchQuery, counts, groupChats, agents, chatDepartments, notificationCount, onlineAgents, userChatEnabled, voiceEnabled } = this.props;
    const { editChat, updateGroup, loadMessages, onChatSearch, onScroll, createGroup, openGroupDrawer, markNewMessages, onSubmit, onSearch, onSearchFocus, onSearchBlur, onClearSearchInput, onRecent, onNotification, onToggleChat, toggleImOverlay } = this.props;
    const { checkedAgents, closeIframes, toggleViewMode, me, myDepartments, teams, recentChats, recentLoaded, overlayShown } = this.props;

    const groupDrawerTarget = document.getElementById('im-button');

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
        <TopBarRecentImList
          me={me}
          agents={agents}
          departments={myDepartments}
          teams={teams}
          chats={recentChats}
          counts={counts}
          onRecentClick={recentClick}
          teamsLoaded={teamsLoaded}
          departmentsLoaded={myDepartmentsLoaded}
          agentsLoaded={agentsLoaded}
          recentLoaded={recentLoaded}
        >
          <IMOverlay
            counts={counts}
            teamsLoaded={teamsLoaded}
            myDepartmentsLoaded={myDepartmentsLoaded}
            agentsLoaded={agentsLoaded}
            me={me}
            agents={agents}
            departments={myDepartments}
            teams={teams}
            onRecentClick={recentClick}
            onParticipantClick={participantClick}
            createNewGroup={() => openGroupDrawer()}
            isOpen={overlayShown}
            chats={recentChats}
            groups={groupChats}
            toggleOverlay={toggleImOverlay}
            departmentsLoaded={myDepartmentsLoaded}
            recentLoaded={recentLoaded}
            groupLoaded={groupLoaded}
            onFocus={onSearchFocus}
            onBlur={onSearchBlur}
            searchQuery={searchQuery}
          >
            <IMButton />
          </IMOverlay>
          {(groupDrawerTarget) ? <GroupAddDrawer
            me={me}
            isOpen={groupCreation}
            agents={agents}
            target={groupDrawerTarget}
            checkedAgents={checkedAgents.toJS()}
            clickOut={() => { this.props.dispatch(chatsActions.closeGroupDrawer()); }}
            createGroup={createGroup}
            updateGroup={updateGroup}
            editChat={editChat}
          /> : null }
          {current.get('id') ? <Container
            isOpen={chating}
            onScroll={onScroll}
            searchQuery={searchQuery}
            onChatSearch={onChatSearch}
            agents={agents}
            departments={myDepartments}
            teams={teams}
            me={me}
            messages={messages}
            current={current}
            onSubmit={onSubmit}
            clickOut={chatClickOut}
            loadMessages={loadMessages}
            loadingMessages={loadingMessages}
            markNewMessages={markNewMessages}
            openGroupDrawer={openGroupDrawer}
          /> : null }

        </TopBarRecentImList>
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
            svg={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/notifications.svg`}
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
