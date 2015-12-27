import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeader, NavFrameBody, SectionsPane, Section, SectionHeader, TabsPane, Tab, ListItem, LabelsDictionary }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { NestedList } from './NestedList';

export class Nav extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    loaded: PropTypes.bool.isRequired,
    dpWindow: PropTypes.object.isRequired,
    users: PropTypes.object.isRequired,
    organizations: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired
  };

  render() {
    const { labels, users, organizations, agents, groupNames, teamNames, dispatch, dpWindow, loaded } = this.props;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <NavFrameHeader icon="icon-dp-streamline-connection-2">
          CRM
        </NavFrameHeader>
        <NavFrameBody>
          <SectionHeader>People</SectionHeader>
          <TabsPane>
            <Tab title="Groups">
              <LoadIndicator loaded={loaded}>
                <ul>
                  <NestedList items={[users.toJS()]} alwaysExpanded/>
                </ul>
              </LoadIndicator>
            </Tab>
            <Tab title="Filters">Filters tab content</Tab>
            <Tab title="Labels">
              <LabelsDictionary labels={labels.get('person')}/>
            </Tab>
          </TabsPane>
          <SectionHeader>Organizations</SectionHeader>
          <TabsPane>
            <Tab title="All">
              <ul>
                <ListItem count={organizations.get('count')} label="All Organizations"/>
              </ul>
            </Tab>
            <Tab title="Labels">
              <LabelsDictionary labels={labels.get('organization')}/>
            </Tab>
          </TabsPane>

           <SectionHeader>Agents</SectionHeader>

           <div className="sidebar-list">
           <ul>
             <LoadIndicator loaded={loaded}>
               <ul>
                 <NestedList items={[agents.toJS()]} alwaysExpanded/>
               </ul>
             </LoadIndicator>
           </ul>
           </div>
        </NavFrameBody>
      </NavFrame>
    );
  }
}
