import React from 'react';
import { TopBar, TopBarItem, TopBarRightMenu, TopBarNotificationIcon } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import AddButton from './AddButton';
import Chat from './Chat';
import User from './User';
import Isvg from 'react-inlinesvg';

class AgentTopBar extends React.Component {
  constructor() {
    super();
    this.state = {
      agents:            [],
      chatDepartments:   [],
      notificationCount: 0,
      user:              null
    };
    window.document.addEventListener('dpUpdateNotifCount', (e) => {
      this.setState({
        notificationCount: e.detail.count
      });
    });
    this.retrieveAgents = this.retrieveAgents.bind(this);
    this.getUserPicture = this.getUserPicture.bind(this);
  }

  componentWillMount() {
    this.retrieveAgents();
    this.retrieveChatDepartments();
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
    if (angularOmnibox) {
      angularOmnibox.searchQuery = searchQuery;
      angularOmnibox.touchSearch();
      window.$('.dp-omnibox-results').show();
    }
  }

  onSearchFocus() {
    window.$('.dp-omnibox-results').show();
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

  getUserPicture() {
    const { user } = this.state;
    if (!user) {
      return '';
    }
    let img = user.avatar.default_url_pattern;
    if (user.avatar.url_pattern) {
      img = user.avatar.url_pattern;
    }
    return img.replace(/\{\{IMG_SIZE}}/, 32);
  }

  closeIframes() {
    for (const key of Object.keys(window.DP_FRAME_OVERLAYS)) {
      const iframe = window.DP_FRAME_OVERLAYS[key];
      if (iframe.opened) {
        iframe.close();
      }
    }
  }

  retrieveAgents() {
    const url = `${window.BASE_URL}api/v2/agents`;
    const self = this;

    // TODO refactor
    window.$.ajax({
      url,
      type:     'GET',
      dataType: 'json',
      headers:  {
        'X-Agent-Request': true
      },
      complete(response) {
        const agents = response.responseJSON.data;
        self.setState({
          agents
        });
        for (const agent of agents) {
          if (parseInt(agent.id, 10) === window.DESKPRO_PERSON_ID) {
            self.setState({
              user: agent
            });
            break;
          }
        }
      }
    });
  }

  retrieveChatDepartments() {
    const url = `${window.BASE_URL}api/v2/chat_departments`;
    const self = this;

    // TODO refactor
    window.$.ajax({
      url,
      type:     'GET',
      dataType: 'json',
      headers:  {
        'X-Agent-Request': true
      },
      complete(response) {
        const chatDepartments = [];
        for (const department of response.responseJSON.data) {
          chatDepartments[department.id] = department;
        }
        self.setState({
          chatDepartments
        });
      }
    });
  }

  toggleViewMode() {
    if (window.DeskPRO_Window.paneVis.tabs) {
      window.DeskPRO_Window.$scope.oneColumnView();
    } else {
      window.DeskPRO_Window.$scope.twoColumnsView();
    }
  }

  render() {
    const { agents, chatDepartments, notificationCount } = this.state;
    return (<TopBar>
      <div className="logo" />
      <TopBarItem classes={['search-box legacy-omnibox']}>
        <SearchBox
          onUserInput={this.onSearch}
          onFocus={this.onSearchFocus}
          onBlur={this.onSearchBlur}
          placeholder="Search ..."
        />
      </TopBarItem>
      <TopBarItem classes={['legacy-omnibox']} onClick={this.onRecent}>
        <i className="icon wait pointer hover" />
      </TopBarItem>
      <AddButton closeIframes={this.closeIframes} />
      <TopBarRightMenu>
        <TopBarItem classes={['view_mode']} onClick={this.toggleViewMode}>
          <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/../src/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/views.svg`} />
        </TopBarItem>
        <TopBarItem classes={['legacy-omnibox']} onClick={this.onNotification}>
          <TopBarNotificationIcon
            elementId="notifications"
            icon="alarm outline hover pointer"
            count={notificationCount}
          />
        </TopBarItem>
        <TopBarItem>
          <User src={this.getUserPicture()} />
          <Chat agents={agents} chatDepartments={chatDepartments} updateVolume={this.onChatVolumeUpdate} volume={8} />
        </TopBarItem>
      </TopBarRightMenu>
    </TopBar>);
  }
}
export default AgentTopBar;
