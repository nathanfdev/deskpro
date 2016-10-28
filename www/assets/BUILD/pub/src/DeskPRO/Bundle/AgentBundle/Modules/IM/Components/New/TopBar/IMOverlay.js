import React, { PropTypes } from 'react';
import Loader from 'react-loader';
import classNames from 'classnames';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { Tabs } from 'DeskPRO/Component/Semantic/Tabs';
import { Segment, SegmentsGroup } from 'DeskPRO/Component/Semantic/Segment';
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
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';

export default class IMOverlay extends React.Component {

  static propTypes = {
    me:                 PropTypes.object.isRequired,
    agents:             PropTypes.object.isRequired,
    departments:        PropTypes.object.isRequired,
    teams:              PropTypes.object.isRequired,
    counts:             PropTypes.object.isRequired,
    chats:              PropTypes.object.isRequired,
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
    groupLoaded:        PropTypes.bool.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      filter: ''
    };
    this.onListFilter = this.onListFilter.bind(this);
  }

  onListFilter(filter) {
    this.setState({ filter });
  }

  getRecentTab() {
    const { agentsLoaded, teamsLoaded, departmentsLoaded, recentLoaded } = this.props;
    const { me, agents, departments, teams, counts, chats, onRecentClick } = this.props;
    const loaded = agentsLoaded && teamsLoaded && departmentsLoaded && recentLoaded;
    const props = {
      me,
      agents,
      departments,
      teams,
      counts,
      chats,
      onRecentClick
    };
    const content = (
      <Loader loaded={loaded} opacity={0} width={4} color="#4696dc">
        <Scrollable vertical>
          <RecentList {...props} filter={this.state.filter} />
        </Scrollable>
      </Loader>
    );
    return  {
      id:    'recent',
      title: <span><i className="fa fa-clock-o dp-im-tab-menu-icon" />Recent</span>,
      content
    };
  }


  getAgentsTab() {
    const { agents, counts, onParticipantClick, agentsLoaded, me } = this.props;
    const content = (
      <Loader loaded={agentsLoaded} opacity={0} width={4} color="#4696dc">
        <Scrollable vertical>
          <AgentList
            filter={this.state.filter}
            agents={agents}
            counts={counts}
            onParticipantClick={onParticipantClick} me={me}
          />
        </Scrollable>
      </Loader>
    );
    return  {
      id:    'agents',
      title: <span><i className="fa fa-user dp-im-tab-menu-icon" />Agents</span>,
      content
    };
  }

  getGroupsTab() {
    const { agents, departments, me, onParticipantClick, createNewGroup, teams, groups, onRecentClick } = this.props;
    const { agentsLoaded, teamsLoaded, departmentsLoaded, groupLoaded } = this.props;
    const loaded = agentsLoaded && teamsLoaded && departmentsLoaded && groupLoaded;
    const content = (
      <Loader loaded={loaded} opacity={0} width={4} color="#4696dc">
        <div style={{ height: '355px' }}>
          <Scrollable vertical>
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
            {groups.size > 0 ? <GroupList
              agents={agents}
              onGroupClick={onRecentClick}
              groups={groups} me={me}
              filter={this.state.filter}
            /> : null}
            <div className="ui divider" />
            <Header level={4} className="group-list" content="department" />
            <DepartmentList
              agents={agents}
              departments={departments}
              me={me}
              onParticipantClick={onParticipantClick}
              filter={this.state.filter}
            />
            <div className="ui divider" />
            <Header level={4} className="group-list" content="teams" />
            { teams.size > 0
              ? <AgentTeamList
                agents={agents}
                teams={teams}
                me={me}
                onParticipantClick={onParticipantClick}
                filter={this.state.filter}
              />
              : null
            }

          </Scrollable>
        </div>
      </Loader>
    );
    return  {
      content,
      id:    'groups',
      title: <span><i className="fa fa-group dp-im-tab-menu-icon" />Groups</span>
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
        <div className="im header">Agent IM</div>
        <SegmentsGroup className="im">
          <Segment className="search-wrapper">
            <SearchBox
              onFocus={onFocus}
              onBlur={onBlur}
              onUserInput={this.onListFilter}
              placeholder="Search ..."
            />
          </Segment>
          <Segment className="im-tabs">{this.getTabs()}</Segment>
        </SegmentsGroup>
      </div>
    );
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
          positionMy="center-25 top"
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
