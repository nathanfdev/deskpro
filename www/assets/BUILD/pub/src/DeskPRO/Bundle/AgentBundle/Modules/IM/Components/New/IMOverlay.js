import React, { PropTypes } from 'react';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { Tabs } from 'DeskPRO/Component/Semantic/Tabs';
import { Segment, SegmentsGroup } from 'DeskPRO/Component/Semantic/Segment';
import AgentList from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/AgentsList';
import DepartmentsList from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/DepartmentsList';
import EveryoneSegment from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/EveryoneSegment';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { Header } from 'DeskPRO/Component/Semantic/Common';

class IMOverlay extends React.Component {

  static propTypes = {
    me:            PropTypes.object.isRequired,
    agents:        PropTypes.object.isRequired,
    departments:   PropTypes.object.isRequired,
    notifications: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      header: 'Agent IM'
    };
  }

  getRecentTab() {
    return  {
      id:      'recent',
      content: 'recent',
      title:   <span><i className="fa fa-clock-o dp-im-tab-menu-icon" />Recent</span>
    };
  }

  getAgentsTab() {
    const { agents, notifications } = this.props;
    return  {
      id:      'agents',
      content: <AgentList agents={agents} notifications={notifications} />,
      title:   <span><i className="fa fa-user dp-im-tab-menu-icon" />Agents</span>
    };
  }

  getGroupsTab() {
    const { agents, departments, me } = this.props;
    const content = (
      <div>
        <Header
          size={4}
          classes={['group-list']}
          content={<span>everyone<span className="agents-counter">({agents.size})</span></span>}
        />
        <EveryoneSegment agents={agents} />
        <div className="ui divider"></div>
        <Header size={4} classes={['group-list']} content="im groups" />
        <Segment classes={['new-im-group']} raised vertical>+ create new im group</Segment>
        <div className="ui divider"></div>
        <Header size={4} classes={['group-list']} content="department" />
        <DepartmentsList agents={agents} departments={departments} me={me} />
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
        <div className="im header">{this.state.header}</div>
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
      classes={['im']}
      positionMy="left top-2"
      positionAt="left bottom"
      id={3}
      zIndex={99999}
      content={this.getContent()}
      autoClose={false}
      autoOpen={false}
      opened
    ><button className="ui button">+</button></PopUp>);
  }
}

export default IMOverlay;
