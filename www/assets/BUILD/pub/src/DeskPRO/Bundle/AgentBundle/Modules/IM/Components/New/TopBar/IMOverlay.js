import React, { PropTypes } from 'react';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { Tabs } from 'DeskPRO/Component/Semantic/Tabs';
import { Segment, SegmentsGroup } from 'DeskPRO/Component/Semantic/Segment';
import { AgentList } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMTabs/AgentList';
import { DepartmentList } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMTabs/DepartmentList';
import { AgentTeamList } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMTabs/AgentTeamList';
import { RecentList } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMTabs/RecentList';
import { EveryoneSegment } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMTabs/EveryoneSegment';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { Header } from 'DeskPRO/Component/Semantic/Common';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';

export class IMOverlay extends React.Component {

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
    createNewGroup:     PropTypes.func.isRequired
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
          <div className="ui divider"></div>
          <Header size={4} classes={['group-list']} content="im groups" />
          <Segment classes={['new-im-group']} raised vertical><span onClick={() => createNewGroup()}>+ create new im group</span></Segment>
          <div className="ui divider"></div>
          <Header size={4} classes={['group-list']} content="department" />
          <DepartmentList agents={agents} departments={departments} me={me} onParticipantClick={onParticipantClick} />
          <div className="ui divider"></div>
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
    return (<PopUp
      classes={['im wrapper']}
      innerClasses={['im']}
      positionMy="center-3 top-5"
      positionAt="center bottom"
      id={100500}
      zIndex={99999}
      content={this.getContent()}
      autoClose={false}
      autoOpen={false}
      opened
    >
      {this.props.children}
    </PopUp>);
  }
}
