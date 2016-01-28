import React, { Component, PropTypes } from 'react';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { NavFrame, NavFrameHeaderContainer, NavFrameBody, SectionHeader, TabsPaneStatefulContainer, Tab, ListItem,
  LabelsDictionary } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { NestedList } from './NestedList';
import { ListItemContainer } from './ListItemContainer';

export class Nav extends Component {

  static propTypes = {
    isLoaded: PropTypes.bool.isRequired,
    users: PropTypes.object.isRequired,
    organizations: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired
  };

  render() {
    const { labels, users, organizations, agents, isLoaded } = this.props;

    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-connection-2">CRM</NavFrameHeaderContainer>
        <NavFrameBody isLoaded={isLoaded}>
          <SectionHeader>People</SectionHeader>
          <TabsPaneStatefulContainer id="peopleTab">
            <Tab title="Groups">
              <NestedList items={[users.toJS()]}
                          isAgent={0}
                          group="people"
                          alwaysExpanded />
            </Tab>
            <Tab title="Filters">Filters tab content</Tab>
            <Tab title="Labels">
              {labels && <LabelsDictionary labels={labels.get('person')} onClick={()=>{}}/>}
            </Tab>
          </TabsPaneStatefulContainer>

          <SectionHeader>Organizations</SectionHeader>
          <TabsPaneStatefulContainer id="orgTab">
            <Tab title="All">
              <ul>
                <ListItemContainer group="organizations"
                                   label="all"
                                   listOptions={{content: 'organizations', sort: 'name', order: constants.ORDER_ASC}}>
                  <ListItem count={organizations.get('count')} label="All Organizations"/>
                </ListItemContainer>
              </ul>
            </Tab>
            <Tab title="Labels">
              {labels && <LabelsDictionary labels={labels.get('organization')} onClick={()=>{}}/>}
            </Tab>
          </TabsPaneStatefulContainer>

          <SectionHeader>Agents</SectionHeader>
            <div className="sidebar-list">
              <NestedList items={[agents.toJS()]}
                          isAgent={1}
                          group="agents"
                          alwaysExpanded />
            </div>
        </NavFrameBody>
      </NavFrame>
    );
  }
}
