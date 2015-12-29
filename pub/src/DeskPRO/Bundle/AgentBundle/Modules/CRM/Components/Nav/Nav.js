import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeader, NavFrameBody, SectionHeader, TabsPaneStatefulContainer, Tab, ListItem, LabelsDictionary }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { NestedList } from './NestedList';
import { ListItemContainer } from './ListItemContainer';

export class Nav extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    loaded: PropTypes.bool.isRequired,
    dpWindow: PropTypes.object.isRequired,
    users: PropTypes.object.isRequired,
    organizations: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired
  };

  render() {
    const { labels, users, organizations, agents, dispatch, dpWindow, loaded } = this.props;
    const currentApp = dpWindow.get('activeAppId');

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <NavFrameHeader icon="icon-dp-streamline-connection-2" currentApp={currentApp}>
          CRM
        </NavFrameHeader>
        <NavFrameBody>

          <SectionHeader>People</SectionHeader>
          <TabsPaneStatefulContainer id="peopleTab">
            <Tab title="Groups">
              <LoadIndicator loaded={loaded}>
                <ul>
                  <NestedList items={[users.toJS()]}
                              isAgent={0}
                              group="people"
                              alwaysExpanded/>
                </ul>
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
                                     listOptions={{content: 'organizations'}}>
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
              <ul>
                <NestedList items={[agents.toJS()]}
                            isAgent={1}
                            group="agents"
                            alwaysExpanded/>
              </ul>
            </div>
          </LoadIndicator>
        </NavFrameBody>
      </NavFrame>
    );
  }
}
