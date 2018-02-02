import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Isvg from 'react-inlinesvg';
import uuid from 'uuid';
import striptags from 'striptags';
import Notify from 'notifyjs';
import linkifyHtml from 'linkifyjs/html';
import $ from 'jquery';
import { AvatarResolver } from 'DeskPRO/Component/Avatar';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { Container } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/ChatWindow';
import * as chatsActions from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Actions/chatsActions';
import * as messagesActions from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Actions/messagesActions';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';
import { TopBar, TopBarItem, TopBarRightMenu, TopBarNotificationIcon } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { isLoadedCollectionSelectorFactory, collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { defaultBrandSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/common';
import { IMOverlay, IMButton, TopBarRecentImList, GroupAddDrawer } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/TopBar';
import AddButton from './AddButton';
import Chat from './Chat';
import User from './User';
import VoiceMenu from '../../Voice/Components/VoiceMenu/VoiceMenuContainer';
import AvatarHelper from '../../IM/Components/New/IMTabs/AvatarHelper';
import { isVoiceAvailableSelector } from '../../Voice/Selectors/client';
import { onlineUserChatAgentsSelector, userChatEnabledSelector } from '../../Agent/Selectors/agents';
import { toggleUserChat } from '../../Agent/Actions/agentActions';
import { closeIframes } from '../../Application/Actions/bootstrapActions';

@connect(state => ({
  agents:              collectionSelectorFactory('Person', 'agents')(state),
  people:              collectionSelectorFactory('Person', 'people')(state),
  chatDepartments:     collectionSelectorFactory('Department', 'all_chat')(state),
  recentChats:         collectionSelectorFactory('AgentChat', 'recent')(state),
  groupChats:          collectionSelectorFactory('AgentChat', 'group')(state),
  startedByMeChats:    state.IM.chats.get('startedByMe'),
  myDepartments:       collectionSelectorFactory('Department', 'my_tickets')(state),
  myTeams:             collectionSelectorFactory('AgentTeam', 'my')(state),
  defaultBrand:        defaultBrandSelector(state),
  me:                  meSelector(state),
  counts:              state.IM.messages.get('counts'),
  current:             state.IM.chats.get('current'),
  editChat:            state.IM.chats.get('editChat'),
  chating:             state.IM.chats.get('chating'),
  overlayShown:        state.IM.chats.get('overlayShown'),
  groupCreation:       state.IM.chats.get('groupCreation'),
  checkedAgents:       state.IM.chats.get('checkedAgents'),
  activeTabs:          state.IM.chats.get('activeTabs'),
  imSettings:          state.Agent.settings.get('im'),
  messages:            state.IM.messages,
  drafts:              state.IM.messages.get('drafts'),
  loadingMessages:     state.IM.messages.get('loadingMessages'),
  updatingMessages:    state.IM.messages.get('updatingMessages'),
  recentLoaded:        isLoadedCollectionSelectorFactory('AgentChat', 'recent')(state),
  groupLoaded:         isLoadedCollectionSelectorFactory('AgentChat', 'group')(state),
  myTeamsLoaded:       isLoadedCollectionSelectorFactory('AgentTeam', 'my')(state),
  myDepartmentsLoaded: isLoadedCollectionSelectorFactory('Department', 'my_tickets')(state),
  agentsLoaded:        isLoadedCollectionSelectorFactory('Person', 'agents')(state),
  voiceAvailable:      isVoiceAvailableSelector(state),
  userChatEnabled:     userChatEnabledSelector(state),
  onlineAgents:        onlineUserChatAgentsSelector(state)
}))
export class AgentTopBarContainer extends SeparateComponent {

  static propTypes = {
    me:                  PropTypes.object,
    dispatch:            PropTypes.func.isRequired,
    agents:              PropTypes.object.isRequired,
    people:              PropTypes.object.isRequired,
    chatDepartments:     PropTypes.object.isRequired,
    myDepartments:       PropTypes.object.isRequired,
    myTeams:             PropTypes.object.isRequired,
    defaultBrand:        PropTypes.object,
    recentChats:         PropTypes.object.isRequired,
    groupChats:          PropTypes.object.isRequired,
    startedByMeChats:    PropTypes.object.isRequired,
    messages:            PropTypes.object,
    drafts:              PropTypes.object,
    counts:              PropTypes.object,
    current:             PropTypes.object,
    editChat:            PropTypes.object,
    checkedAgents:       PropTypes.object,
    activeTabs:          PropTypes.object,
    imSettings:          PropTypes.object,
    recentLoaded:        PropTypes.bool.isRequired,
    groupLoaded:         PropTypes.bool.isRequired,
    loadingMessages:     PropTypes.bool.isRequired,
    updatingMessages:    PropTypes.bool.isRequired,
    chating:             PropTypes.bool.isRequired,
    overlayShown:        PropTypes.bool.isRequired,
    myTeamsLoaded:       PropTypes.bool.isRequired,
    myDepartmentsLoaded: PropTypes.bool.isRequired,
    agentsLoaded:        PropTypes.bool.isRequired,
    groupCreation:       PropTypes.bool.isRequired
  };

  static addLinks(message) {
    const linkedMessage = linkifyHtml(message);
    function replacer(match, scheme) {
      if (!scheme) {
        return `${match}http://`;
      }
      return match;
    }
    return linkedMessage.replace(/<a[^>]+href="([a-z]+:\/\/)?/gi, replacer);
  }

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
      const event = new Event('dpPopupOpen', { detail: { mode: 'search' } });
      window.document.dispatchEvent(event);
    }
  }

  recentClick = (chatId) => {
    const { current, dispatch, chating } = this.props;

    if (current && chatId === current.get('id') && chating) {
      dispatch(chatsActions.closeChat(chatId));
    } else {
      dispatch(chatsActions.startChat(null, chatId));
    }
  };

  componentDidMount() {
    if (Notify.needsPermission && Notify.isSupported()) {
      Notify.requestPermission();
    }
    if (this.props.defaultBrand) {
      AvatarHelper.setBrandLogoUrl(this.props.defaultBrand.get('logo_url'));
    }
  }

  participantClick = (id, type) => {
    this.props.dispatch(chatsActions.startChat({ id, type }));
  };

  onChatClose = (chatId) => {
    this.props.dispatch(chatsActions.closeChat(chatId));
  };

  saveDraft = (chatId, draft) => {
    this.props.dispatch(messagesActions.saveDraft(chatId, draft));
  };

  onHideChat = (chat) => {
    this.props.dispatch(chatsActions.hideChat(chat));
  };

  onSubmitChatMessage = (message) => {
    let testMessage = striptags(message, ['img', 'svg', 'video', 'object', 'embed']);
    testMessage = testMessage.replace(/(&nbsp;\s)+$/g, '');
    testMessage = testMessage.replace(/(&nbsp;|\s)+$/g, '');
    testMessage = testMessage.replace(/^(&nbsp;|\s)+/g, '');
    testMessage = testMessage.replace(/(&nbsp;|\s)+$/g, '');

    if (testMessage.trim() || message.indexOf('<video') !== -1) { // another dancing around froala, it wraps <video> into <span>
      const { dispatch, current, me } = this.props;
      const linkedMessage = AgentTopBarContainer.addLinks(message);
      dispatch(messagesActions.addMessage(current.get('id'), linkedMessage, uuid(), me));
    }
  };

  updateChatsOrder = (chats) => {
    const order = {};
    chats.forEach((chat) => {
      order[chat.get('id')] = chat.get('order');
    });
    this.props.dispatch(chatsActions.updateChatsOrder(order));
  };

  componentWillMount = () => {
    const oldNotifIcon = document.getElementById('notifs_counts');
    if (oldNotifIcon.innerHTML > 0) {
      this.setState({
        notificationCount: parseInt(oldNotifIcon.innerHTML, 10)
      });
    }
    if (window.DP_HAS_NEW_IM) {
      this.props.dispatch(chatsActions.loadRecentChats());
      this.props.dispatch(chatsActions.loadGroups());

      this.refreshCounts();
    }
  };

  onSearchFocus = () => {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
    if (this.agentTopBar && this.agentTopBar.searchBox.textInput.value) {
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
      const event = new Event('dpPopupOpen', { detail: { mode: 'recent' } });
      window.document.dispatchEvent(event);
    }
  }

  static onNotification() {
    const angularOmnibox = window.angular.element('.dp-omnibox').scope();
    if (angularOmnibox) {
      angularOmnibox.toggleMode('notif');
      const event = new Event('dpPopupOpen', { detail: { mode: 'notif' } });
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

  onToggleChat = (enabled) => {
    this.props.dispatch(toggleUserChat(enabled));
  };

  toggleImOverlay = () => {
    this.props.dispatch(chatsActions.toggleOverlay());
  };

  openGroupDrawer = (agentIds = [], chat = null) => {
    this.props.dispatch(chatsActions.openGroupDrawer(agentIds, chat));
  };

  createGroup = (agentIds, groupName) => {
    this.props.dispatch(chatsActions.startChat({ id: agentIds, type: 'group', name: groupName }));
  };

  updateGroup = (chatId, ids, name) => {
    this.props.dispatch(chatsActions.updateChat(chatId, ids, name));
  };

  deleteGroup = (chat) => {
    this.props.dispatch(chatsActions.deleteGroup(chat.get('id')));
  };

  leaveGroup = (chat) => {
    this.props.dispatch(chatsActions.leaveGroup(chat.get('id')));
  };

  getPath = () => {
    let path;
    if (!this.state.searchQuery) {
      path = ['chatMessages', this.props.current.get('id')];
    } else {
      path = ['searchMessages', this.props.current.get('id')];
    }
    return path;
  };

  markNewMessages = () => {
    const { updatingMessages, current, dispatch, counts } = this.props;
    if (!updatingMessages && counts.nested && counts.nested[current.get('id')] && counts.nested[current.get('id')].count > 0) {
      dispatch(messagesActions.markAllMessagesAsRead(current.get('id')));
    }
  };

  onScroll = (object) => {
    const messages = this.props.messages.getIn(this.getPath());
    let moveScroll = false;
    if (messages) {
      const page = messages.page;
      const maxPage = messages.pages;
      if (object.atTheTop && !this.props.loadingMessages && page < maxPage) {
        this.loadMessages(page + 1);
        moveScroll = true;
      }

      $('.chatDivider').each((index, element) => { // eslint-disable-line
        const $currentChatContext = $(`#chat-container-${this.props.current.get('id')}`);
        if (
          $(element).offset().top > $currentChatContext.offset().top
          && $(element).offset().top < $currentChatContext.offset().top + object.containerHeight
          && $(element).data('page') - 1 > 0
          && !messages.pagesLoaded[$(element).data('page')]
          && !this.props.loadingMessages
        ) {
          this.loadMessages($(element).data('page'));
          return false;
        }
      });
    }

    return moveScroll;
  };

  onChatSearch = (searchQuery) => {
    let reload = false;
    if ((this.state.searchQuery !== searchQuery && searchQuery.length > 2) || searchQuery === '') {
      reload = true;
    }
    if (reload && !this.props.loadingMessages) {
      this.setState({ searchQuery, page: 1 }, () => { this.loadMessages(1); });
    }
  };

  loadMessages = (page = 1) => {
    const { current, dispatch } = this.props;
    if (current.get('id')) {
      dispatch(messagesActions.loadMessages(current.get('id'), this.state.searchQuery, page));
    }
  };

  onSearchMessageClick = (message) => {
    this.props.dispatch(messagesActions.searchMessageClick(message));
  };

  render() {
    const props = {
      ...this.props,
      updateVolume:         AgentTopBarContainer.updateVolume,
      onSearch:             AgentTopBarContainer.onSearch,
      onSearchFocus:        this.onSearchFocus,
      onSearchBlur:         AgentTopBarContainer.onSearchBlur,
      toggleViewMode:       AgentTopBarContainer.toggleViewMode,
      onRecent:             AgentTopBarContainer.onRecent,
      onNotification:       AgentTopBarContainer.onNotification,
      onClearSearchInput:   AgentTopBarContainer.onClearSearchInput,
      notificationCount:    this.state.notificationCount,
      toggleImOverlay:      this.toggleImOverlay,
      onChatClose:          this.onChatClose,
      saveDraft:            this.saveDraft,
      recentClick:          this.recentClick,
      participantClick:     this.participantClick,
      onSubmitChatMessage:  this.onSubmitChatMessage,
      markNewMessages:      this.markNewMessages,
      openGroupDrawer:      this.openGroupDrawer,
      createGroup:          this.createGroup,
      updateGroup:          this.updateGroup,
      onScroll:             this.onScroll,
      loadMessages:         this.loadMessages,
      onChatSearch:         this.onChatSearch,
      searchQuery:          this.state.searchQuery,
      onToggleChat:         this.onToggleChat,
      onHideChat:           this.onHideChat,
      deleteGroup:          this.deleteGroup,
      leaveGroup:           this.leaveGroup,
      onSearchMessageClick: this.onSearchMessageClick,
      updateChatsOrder:     this.updateChatsOrder
    };
    return <AgentTopBar {...props} ref={(c) => { this.agentTopBar = c; }} />;
  }
}
export class AgentTopBar extends React.Component {

  static propTypes = {
    agents:               PropTypes.object.isRequired,
    people:               PropTypes.object.isRequired,
    chatDepartments:      PropTypes.object.isRequired,
    myDepartments:        PropTypes.object.isRequired,
    myTeams:              PropTypes.object.isRequired,
    me:                   PropTypes.object.isRequired,
    recentChats:          PropTypes.object.isRequired,
    groupChats:           PropTypes.object.isRequired,
    startedByMeChats:     PropTypes.object.isRequired,
    notificationCount:    PropTypes.number,
    dispatch:             PropTypes.func.isRequired,
    updateVolume:         PropTypes.func,
    onSearch:             PropTypes.func,
    onSearchFocus:        PropTypes.func,
    onSearchBlur:         PropTypes.func,
    onRecent:             PropTypes.func,
    onNotification:       PropTypes.func,
    toggleViewMode:       PropTypes.func,
    toggleImOverlay:      PropTypes.func,
    openGroupDrawer:      PropTypes.func,
    onChatClose:          PropTypes.func,
    saveDraft:            PropTypes.func,
    recentClick:          PropTypes.func,
    participantClick:     PropTypes.func,
    createGroup:          PropTypes.func,
    updateGroup:          PropTypes.func,
    onSubmitChatMessage:  PropTypes.func,
    markNewMessages:      PropTypes.func,
    onScroll:             PropTypes.func,
    loadMessages:         PropTypes.func,
    onChatSearch:         PropTypes.func,
    onSearchMessageClick: PropTypes.func,
    updateChatsOrder:     PropTypes.func,
    messages:             PropTypes.object,
    drafts:               PropTypes.object,
    counts:               PropTypes.object,
    current:              PropTypes.object,
    editChat:             PropTypes.object,
    checkedAgents:        PropTypes.object,
    activeTabs:           PropTypes.object,
    imSettings:           PropTypes.object,
    searchQuery:          PropTypes.string,
    loadingMessages:      PropTypes.bool.isRequired,
    groupCreation:        PropTypes.bool.isRequired,
    chating:              PropTypes.bool.isRequired,
    overlayShown:         PropTypes.bool.isRequired,
    recentLoaded:         PropTypes.bool.isRequired,
    groupLoaded:          PropTypes.bool.isRequired,
    myTeamsLoaded:        PropTypes.bool.isRequired,
    myDepartmentsLoaded:  PropTypes.bool.isRequired,
    agentsLoaded:         PropTypes.bool.isRequired,
    onClearSearchInput:   PropTypes.func,
    voiceAvailable:       PropTypes.bool,
    userChatEnabled:      PropTypes.bool,
    onlineAgents:         PropTypes.object,
    onToggleChat:         PropTypes.func,
    onHideChat:           PropTypes.func,
    deleteGroup:          PropTypes.func,
    leaveGroup:           PropTypes.func
  };

  onChatVolumeUpdate = (newVal) => {
    this.props.updateVolume(newVal / 10);
  };

  renderIM() {
    if (!window.DP_HAS_NEW_IM) return null;

    const { groupCreation, myTeamsLoaded, myDepartmentsLoaded, agentsLoaded, loadingMessages } = this.props;
    const { current, chating, messages, onChatClose, participantClick, recentClick, createGroup } = this.props;
    const { onChatSearch, onScroll, openGroupDrawer, markNewMessages, onSubmitChatMessage } = this.props;
    const { toggleImOverlay, searchQuery, counts, groupChats, checkedAgents, me, myDepartments, myTeams } = this.props;
    const { recentChats, agents, people, onSearchFocus, onSearchBlur, editChat, updateGroup, loadMessages } = this.props;
    const { activeTabs, recentLoaded, groupLoaded, overlayShown, startedByMeChats, drafts, saveDraft }  = this.props;
    const { leaveGroup, deleteGroup, onHideChat, onSearchMessageClick, imSettings, updateChatsOrder } = this.props;

    const groupDrawerTarget = document.getElementById('im-button');

    return (<TopBarItem className="im" childrenWrapper="im-list">
      <TopBarRecentImList
        me={me}
        current={current}
        chating={chating}
        agents={agents}
        people={people}
        departments={myDepartments}
        teams={myTeams}
        chats={recentChats}
        imSettings={imSettings}
        startedByMeChats={startedByMeChats}
        counts={counts}
        onRecentClick={recentClick}
        teamsLoaded={myTeamsLoaded}
        departmentsLoaded={myDepartmentsLoaded}
        agentsLoaded={agentsLoaded}
        recentLoaded={recentLoaded}
        onHideChat={onHideChat}
        updateChatsOrder={updateChatsOrder}
      >
        <IMOverlay
          counts={counts}
          teamsLoaded={myTeamsLoaded}
          myDepartmentsLoaded={myDepartmentsLoaded}
          agentsLoaded={agentsLoaded}
          me={me}
          agents={agents}
          people={people}
          departments={myDepartments}
          teams={myTeams}
          onRecentClick={recentClick}
          onParticipantClick={participantClick}
          createNewGroup={() => openGroupDrawer()}
          isOpen={overlayShown}
          chats={recentChats}
          startedByMeChats={startedByMeChats}
          groups={groupChats}
          toggleOverlay={toggleImOverlay}
          departmentsLoaded={myDepartmentsLoaded}
          recentLoaded={recentLoaded}
          groupLoaded={groupLoaded}
          onFocus={onSearchFocus}
          onBlur={onSearchBlur}
          searchQuery={searchQuery}
          deleteGroup={deleteGroup}
          leaveGroup={leaveGroup}
          dispatch={this.props.dispatch}
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
          onAgentClick={participantClick}
          agents={agents}
          people={people}
          departments={myDepartments}
          teams={myTeams}
          me={me}
          messages={messages}
          drafts={drafts}
          current={current}
          activeTabs={activeTabs}
          onSubmit={onSubmitChatMessage}
          onClose={onChatClose}
          saveDraft={saveDraft}
          loadMessages={loadMessages}
          loadingMessages={loadingMessages}
          markNewMessages={markNewMessages}
          openGroupDrawer={openGroupDrawer}
          onSearchMessageClick={onSearchMessageClick}
        /> : null }
      </TopBarRecentImList>
    </TopBarItem>);
  }

  render() {
    const { me, agents, chatDepartments, notificationCount, onlineAgents, userChatEnabled, voiceAvailable } = this.props;
    const { onSearch, onSearchFocus, onSearchBlur, onClearSearchInput, onRecent } = this.props;
    const { toggleViewMode, onNotification, onToggleChat } = this.props;

    return (<TopBar>
      <TopBarItem className="search-box legacy-omnibox">
        <SearchBox
          onUserInput={onSearch}
          onFocus={onSearchFocus}
          onBlur={onSearchBlur}
          onClearInput={onClearSearchInput}
          placeholder={`${agentPhrases.get('agent.chrome.nav_search')} ...`}
          ref={(c) => { this.searchBox = c; }}
          icon={<Isvg className="search" src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/general/search.svg`} />}
        />
      </TopBarItem>
      <TopBarItem
        className="legacy-omnibox recent"
        onClick={onRecent}
        title={agentPhrases.get('agent.chrome.recent_tooltip')}
      >
        <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/recent.svg`} />
      </TopBarItem>
      { this.renderIM() }
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
          <AvatarResolver avatar={me && me.get('avatar')} size={56}>
            <User />
          </AvatarResolver>
          <Chat
            activeChat={userChatEnabled}
            onlineAgents={onlineAgents}
            agents={agents.toArray()}
            chatDepartments={chatDepartments.toArray()}
            updateVolume={this.onChatVolumeUpdate}
            volume={8}
            onToggleChat={onToggleChat}
          />
          {window.DP_HAS_VOICE && voiceAvailable && <VoiceMenu />}
        </TopBarItem>
      </TopBarRightMenu>
    </TopBar>
    );
  }
}

export default AgentTopBar;
