import React from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, TabsPane, Tab, ListItem, LabelsDictionary }
       from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

export class Nav extends React.Component {
  render() {
    const { labels, users, organizations, agents, groupNames, teamNames, dispatch, dp_window } = this.props;

    let itemKey = 0;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dp_window={dp_window}>
        <NavFrameHeader icon="fa-users" dispatch={dispatch.bind(this)}>CRM</NavFrameHeader>
            <SectionHeader>People</SectionHeader>
            <TabsPane>
              <Tab title="Groups">
                <ul>
                  <ListItem count={users.total} label="Everyone" />
                  {users.groups.map(item =>
                      <ListItem key={itemKey++} count={item.count} label={groupNames[item.group]} />)}
                </ul>
              </Tab>
              <Tab title="Filters">Filters tab content</Tab>
              <Tab title="Labels">
                <LabelsDictionary labels={labels.person} />
              </Tab>
            </TabsPane>
            <SectionHeader>Organizations</SectionHeader>
            <TabsPane>
              <Tab title="All">
                <ul>
                  <ListItem count={organizations.total} label="All Organizations" />
                </ul>
              </Tab>
              <Tab title="Labels">
                <LabelsDictionary labels={labels.organization} />
              </Tab>
            </TabsPane>

            <SectionHeader>Agents</SectionHeader>
            <ul>
              <ListItem count={agents.total} label="All Agents" />
              {agents.teams.map(item =>
                  <ListItem key={itemKey++} count={item.count} label={teamNames[item.group]} />)}
            </ul>
      </NavFrame>
    );
  }
}
