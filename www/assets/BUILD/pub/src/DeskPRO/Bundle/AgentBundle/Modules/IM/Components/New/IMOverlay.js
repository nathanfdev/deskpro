import React from 'react';
import { PopUp } from 'Semantic/PopUp';
import { Tabs } from 'Semantic/Tabs';
import { Segment, SegmentsGroup } from 'Semantic/Segment';
import SearchBox from 'Semantic/SearchBox';

class IMOverlay extends React.Component {

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
    return  {
      id:      'agents',
      content: 'agents',
      title:   <span><i className="fa fa-user dp-im-tab-menu-icon" />Agents</span>
    };
  }

  getGroupsTab() {
    return  {
      id:      'groups',
      content: 'groups',
      title:   <span><i className="fa fa-group dp-im-tab-menu-icon" />Groups</span>
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
