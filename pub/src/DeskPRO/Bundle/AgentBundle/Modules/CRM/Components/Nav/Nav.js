import React, { Component, PropTypes } from 'react';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { NavFrame, NavFrameHeaderContainer, NavFrameBody, SectionHeader, TabsPaneStatefulContainer, Tab, ListItem,
  LabelsDictionary } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { NestedList } from './NestedList';
import { ListItemContainer } from './ListItemContainer';

export class Nav extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    users: PropTypes.object.isRequired,
    organizations: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired
  };

  render() {
    const { labels, users, organizations, agents, loaded } = this.props;

    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-connection-2">CRM</NavFrameHeaderContainer>
        <NavFrameBody>
          <SectionHeader>People</SectionHeader>
          <TabsPaneStatefulContainer id="peopleTab">
            <Tab title="Groups">
              <LoadIndicator loaded={loaded}>
                <NestedList items={[users.toJS()]}
                            isAgent={0}
                            group="people"
                            alwaysExpanded/>
              </LoadIndicator>
            </Tab>
            <Tab title="Filters">Filters tab content</Tab>
            <Tab title="Labels">
              <LoadIndicator loaded={loaded}>
                {labels && <LabelsDictionary labels={labels.get('person')} onClick={()=>{}}/>}
              </LoadIndicator>
            </Tab>
          </TabsPaneStatefulContainer>

          <SectionHeader>Organizations</SectionHeader>
          <TabsPaneStatefulContainer id="orgTab">
            <Tab title="All">
              <LoadIndicator loaded={loaded}>
                <ul>
                  <ListItemContainer group="organizations"
                                     label="all"
                                     listOptions={{content: 'organizations', sort: 'name', order: constants.ORDER_ASC}}>
                    <ListItem count={organizations.get('count')} label="All Organizations"/>
                  </ListItemContainer>
                </ul>
              </LoadIndicator>
            </Tab>
            <Tab title="Labels">
              <LoadIndicator loaded={loaded}>
                {labels && <LabelsDictionary labels={labels.get('organization')} onClick={()=>{}}/>}
              </LoadIndicator>
            </Tab>
          </TabsPaneStatefulContainer>

          <SectionHeader>Agents</SectionHeader>
          <LoadIndicator loaded={loaded}>
            <div className="sidebar-list">
              <NestedList items={[agents.toJS()]}
                          isAgent={1}
                          group="agents"
                          alwaysExpanded/>
            </div>
          </LoadIndicator>
        </NavFrameBody>
      </NavFrame>
    );
  }
}
