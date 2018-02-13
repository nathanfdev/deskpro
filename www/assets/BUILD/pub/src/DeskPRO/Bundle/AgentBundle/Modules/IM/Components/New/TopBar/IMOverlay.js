import PropTypes from 'prop-types';
import React from 'react';
import Loader from '@deskpro/react-loader';
import classNames from 'classnames';
import debounce from 'lodash/debounce';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { Tabs } from 'DeskPRO/Component/Semantic/Tabs';
import { Segment, SegmentsGroup } from 'DeskPRO/Component/Semantic/Segment';
import { loadActiveTabs } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Actions/chatsActions';
import {
  AgentList,
  DepartmentList,
  AgentTeamList,
  RecentList,
  GroupList,
  EveryoneSegment }
  from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMTabs';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { Header } from 'DeskPRO/Component/Semantic/Common';

export default class IMOverlay extends React.Component {

  static propTypes = {
    me:                 PropTypes.object.isRequired,
    agents:             PropTypes.object.isRequired,
    departments:        PropTypes.object.isRequired,
    teams:              PropTypes.object.isRequired,
    counts:             PropTypes.object.isRequired,
    chats:              PropTypes.object.isRequired,
    startedByMeChats:   PropTypes.object.isRequired,
    groups:             PropTypes.object.isRequired,
    children:           PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
    onRecentClick:      PropTypes.func.isRequired,
    onParticipantClick: PropTypes.func.isRequired,
    createNewGroup:     PropTypes.func.isRequired,
    toggleOverlay:      PropTypes.func.isRequired,
    onFocus:            PropTypes.func.isRequired,
    onBlur:             PropTypes.func.isRequired,
    isOpen:             PropTypes.bool.isRequired,
    recentLoaded:       PropTypes.bool.isRequired,
    teamsLoaded:        PropTypes.bool.isRequired,
    departmentsLoaded:  PropTypes.bool.isRequired,
    agentsLoaded:       PropTypes.bool.isRequired,
    groupLoaded:        PropTypes.bool.isRequired,
    deleteGroup:        PropTypes.func.isRequired,
    leaveGroup:         PropTypes.func.isRequired,
    dispatch:           PropTypes.func.isRequired
  };

  static filterList(list, filter, titleProp) {
    if (filter) {
      return list.filter(item => item.get(titleProp).test(new RegExp(filter, 'gi')));
    }
    return list;
  }

  constructor(props) {
    super(props);
    this.state = {
      filter: ''
    };
  }

  componentWillMount() {
    const { dispatch } = this.props;
    window.addEventListener('keyup', this.onEscape);

    const debouncedDispatchLoadActiveTabs = debounce(() => dispatch(loadActiveTabs()), 1000);

    debouncedDispatchLoadActiveTabs();
    if (window.DeskPRO_Window && window.DeskPRO_Window.TabBar) {
      window.DeskPRO_Window.TabBar.addEvent('addTab', () => debouncedDispatchLoadActiveTabs());
      window.DeskPRO_Window.TabBar.addEvent('closeTab', () => debouncedDispatchLoadActiveTabs());
      window.DeskPRO_Window.TabBar.addEvent('removeTab', () => debouncedDispatchLoadActiveTabs());
    }
  }

  componentWillUnmount() {
    window.removeEventListener('keyup', this.onEscape);
  }

  onEscape = (e) => {
    if (e.keyCode === 27 && this.props.isOpen === true) {
      this.props.toggleOverlay();
    }
  };

  onListFilter = (filter) => {
    this.setState({ filter });
  };

  getHeader(header, list) {
    if (this.state.filter) {
      return `${header} (${list.size})`;
    }
    return header;
  }


  getRecentTab() {
    const { agentsLoaded, teamsLoaded, departmentsLoaded, recentLoaded } = this.props;
    const { me, agents, departments, teams, startedByMeChats, counts, onRecentClick } = this.props;
    const loaded = agentsLoaded && teamsLoaded && departmentsLoaded && recentLoaded;
    const chats = this.filterRecent();
    const props = {
      me,
      agents,
      departments,
      teams,
      counts,
      onRecentClick,
      chats,
      startedByMeChats
    };
    const content = (
      <Loader loaded={loaded} opacity={0} width={4} color="#4696dc">
        <RecentList {...props} />
      </Loader>
    );

    return  {
      id:        'recent',
      title:     <span><i className="fa fa-clock-o dp-im-tab-menu-icon" />{this.getHeader(agentPhrases.get('agent.chrome.btn_recent'), chats)}</span>,
      className: 'native-bars',
      content
    };
  }


  getAgentsTab() {
    const { agents, counts, onParticipantClick, agentsLoaded, me } = this.props;
    const filteredAgents = IMOverlay.filterList(agents, this.state.filter, 'name');

    const content = (
      <Loader loaded={agentsLoaded} opacity={0} width={4} color="#4696dc">
        <AgentList
          agents={filteredAgents}
          counts={counts}
          onParticipantClick={onParticipantClick} me={me}
        />
      </Loader>
    );

    return  {
      id:        'agents',
      title:     <span><i className="fa fa-user dp-im-tab-menu-icon" />{this.getHeader(agentPhrases.get('agent.general.agents'), filteredAgents)}</span>,
      className: 'native-bars',
      content
    };
  }

  getGroupsTab() {
    const { agents, departments, me, onParticipantClick, createNewGroup, teams, groups, onRecentClick } = this.props;
    const { agentsLoaded, teamsLoaded, departmentsLoaded, groupLoaded, deleteGroup, leaveGroup } = this.props;
    const loaded = agentsLoaded && teamsLoaded && departmentsLoaded && groupLoaded;
    const { filter } = this.state;

    const filteredGroups = IMOverlay.filterList(groups, filter, 'name');
    const filteredTeams = IMOverlay.filterList(teams, filter, 'name');
    const filteredDepartments = IMOverlay.filterList(departments, filter, 'title');

    const content = (
      <Loader loaded={loaded} opacity={0} width={4} color="#4696dc">
        <Header
          level={4}
          className="group-list"
          content={<span>everyone<span className="agents-counter">({agents.size})</span></span>}
        />
        <EveryoneSegment agents={agents} onParticipantClick={onParticipantClick} />
        <div className="ui divider" />
        <Header level={4} className="group-list" content="im groups" />
        <Segment className="new-im-group" raised vertical>
          <span onClick={() => createNewGroup()}>
            {groups.size < 1 ? '+ create new im group' : '+ new'}
          </span>
        </Segment>
        {filteredGroups.size > 0 ? <GroupList
          agents={agents}
          onGroupClick={onRecentClick}
          deleteGroup={deleteGroup}
          leaveGroup={leaveGroup}
          groups={filteredGroups}
          me={me}
        /> : null}

        { filteredDepartments.size > 0 ? [
          <div className="ui divider" />,
          <Header level={4} className="group-list" content="department" />,
          <DepartmentList
            agents={agents}
            departments={filteredDepartments}
            me={me}
            onParticipantClick={onParticipantClick}
          />
        ] : null }

        { filteredTeams.size > 0 ? [
          <div className="ui divider" />,
          <Header level={4} className="group-list" content="teams" />,
          <AgentTeamList
            agents={agents}
            teams={filteredTeams}
            me={me}
            onParticipantClick={onParticipantClick}
          />
        ] : null}
      </Loader>
    );

    let header = agentPhrases.get('agent.general.groups');
    if (this.state.filter) {
      header = `${header} (${filteredGroups.size + filteredDepartments.size + filteredTeams.size})`;
    }

    return  {
      content,
      id:        'groups',
      title:     <span><i className="fa fa-group dp-im-tab-menu-icon" />{header}</span>,
      className: 'native-bars'
    };
  }

  getTabs() {
    const recent = this.getRecentTab();
    const agents = this.getAgentsTab();
    const groups = this.getGroupsTab();

    const tabsStructure = {
      items:   [recent, agents, groups],
      classes: {
        menuItem:          ['im'],
        notActiveMenuItem: ['unactive']
      }
    };

    return <Tabs {...tabsStructure} />;
  }

  getContent() {
    const { onFocus, onBlur } = this.props;

    return (
      <div>
        <div className="im header">{agentPhrases.get('agent.chrome.nav_agentchat')}</div>
        <SegmentsGroup className="im">
          <Segment className="search-wrapper">
            <SearchBox
              text={this.state.filter}
              focusOnMount
              onFocus={onFocus}
              onBlur={onBlur}
              onUserInput={this.onListFilter}
              placeholder={agentPhrases.get('agent.general.search')}
            />
          </Segment>
          <Segment className="im-tabs">{this.getTabs()}</Segment>
        </SegmentsGroup>
      </div>
    );
  }

  filterRecent() {
    let { chats } = this.props;
    const { agents, departments, teams, me } = this.props;
    const { filter } = this.state;
    if (filter) {
      chats = chats.filter((chat) => {
        switch (chat.get('chat_type')) {
          case 'agent': {
            let agentId = 0;
            chat.get('agents').forEach((item) => {
              if (item !== me.get('id')) {
                agentId = item;
              }
            });
            const agent = agents.get(agentId);
            return agent ? (agent.get('name') || '').test(new RegExp(filter, 'gi')) : true;
          }
          case 'department':
            return (departments.getIn([chat.getIn(['departments', 0]), 'title']) || '').test(new RegExp(filter, 'gi'));
          case 'team':
            return (teams.getIn([chat.getIn(['agent_teams', 0]), 'name']) || '').test(new RegExp(filter, 'gi'));
          case 'group':
            return (chat.get('name') || '').test(new RegExp(filter, 'gi'));
          case 'everyone':
            return 'everyone'.indexOf(filter) !== -1;
          default:
            return false;
        }
      });
    }

    return chats;
  }

  render() {
    const { isOpen, children, toggleOverlay } = this.props;

    return (
      <div
        className={classNames({ active: isOpen }, ['im', 'wrapper'])}
        ref={(c) => { this.imButton = c; }}
        onClick={toggleOverlay}
        onMouseEnter={this.onMouseEnter}
      >
        {children}
        <Detached
          positionMy="center-20 top"
          zIndex={99999}
          isOpen={isOpen}
          positionTarget={this.imButton}
          {...this.props}
        >
          <ClickOut onClickOut={toggleOverlay} ignoreNodes={['.im.recent .im.wrapper']}>
            <div className={classNames(['ui', 'popup', 'im', 'tabs', 'center', 'bottom'], { visible: isOpen })}>
              {this.getContent()}
            </div>
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
