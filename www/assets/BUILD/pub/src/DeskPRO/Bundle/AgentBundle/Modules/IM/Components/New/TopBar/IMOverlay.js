import React, { PropTypes } from 'react';
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
    notifications:      PropTypes.object.isRequired,
    chats:              PropTypes.object.isRequired,
    children:           PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
    onRecentClick:      PropTypes.func.isRequired,
    onParticipantClick: PropTypes.func.isRequired,
    createNewGroup:     PropTypes.func.isRequired,
    toggleOverlay:      PropTypes.func.isRequired,
    isOpen:             PropTypes.bool.isRequired
  };

  getRecentTab() {
    return  {
      id:      'recent',
      content: <Scrollable vertical><RecentList {...this.props} /></Scrollable>,
      title:   <span><i className="fa fa-clock-o dp-im-tab-menu-icon" />Recent</span>
    };
  }

  getAgentsTab() {
    const { agents, notifications, onParticipantClick } = this.props;
    return  {
      id:      'agents',
      content: <Scrollable vertical><AgentList agents={agents} notifications={notifications} onParticipantClick={onParticipantClick} /></Scrollable>,
      title:   <span><i className="fa fa-user dp-im-tab-menu-icon" />Agents</span>
    };
  }

  getGroupsTab() {
    const { agents, departments, me, onParticipantClick, createNewGroup, teams } = this.props;
    const content = (
      <div style={{ height: '355px' }}>
        <Scrollable vertical>
          <Header
            size={4}
            classes={['group-list']}
            content={<span>everyone<span className="agents-counter">({agents.size})</span></span>}
          />
          <EveryoneSegment agents={agents} onParticipantClick={onParticipantClick} />
          <div className="ui divider" />
          <Header size={4} classes={['group-list']} content="im groups" />
          <Segment classes={['new-im-group']} raised vertical><span onClick={() => createNewGroup()}>+ create new im group</span></Segment>
          <div className="ui divider" />
          <Header size={4} classes={['group-list']} content="department" />
          <DepartmentList agents={agents} departments={departments} me={me} onParticipantClick={onParticipantClick} />
          <div className="ui divider" />
          <Header size={4} classes={['group-list']} content="teams" />
          <AgentTeamList agents={agents} teams={teams} me={me} onParticipantClick={onParticipantClick} />
        </Scrollable>
      </div>
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
    return (
      <div>
        <div className="im header">Agent IM</div>
        <SegmentsGroup classes={['im']}>
          <Segment classes={['search-wrapper']}>
            <SearchBox
              placeholder="Search ..."
            />
          </Segment>
          <Segment classes={['im-tabs']}>{this.getTabs()}</Segment>
        </SegmentsGroup>
      </div>
    );
  }

  render() {
    const { isOpen, children, toggleOverlay } = this.props;

    return (
      <div
        style={{ display: 'inline-block' }}
        className={classNames({ active: isOpen }, ['im', 'wrapper'])}
        ref={c => { this.imButton = c; }}
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
          <ClickOut onClickOut={toggleOverlay}>
            <div className={classNames(['ui', 'popup', 'im', 'tabs', 'center', 'bottom'], { visible: isOpen })}>
              {this.getContent()}
            </div>
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
