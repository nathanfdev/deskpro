import React from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, TabsPane, Tab, ListItem, LabelsDictionary }
       from 'DeskPRO/Bundle/Agentbundle/Modules/Application/Components/NavFrame/index';

export class Nav extends React.Component {
  render() {
    const { labels, groups, organizations, teams, groupNames, teamNames } = this.props;

    let itemKey = 0;

    return (
      <NavFrame>
        <NavFrameHeader icon="fa-users">CRM</NavFrameHeader>
        <SectionsPane>
          <Section>
            <SectionHeader>People</SectionHeader>
            <TabsPane>
              <Tab title="Groups">
                <ul>
                  {groups.items.map(item =>
                      <ListItem key={itemKey++} count={item.count} label={groupNames[item.group]} />)}
                </ul>
              </Tab>
              <Tab title="Filters">Filters tab content</Tab>
              <Tab title="Labels">
                <LabelsDictionary labels={labels.people} />
              </Tab>
            </TabsPane>
          </Section>

          <Section>
            <SectionHeader>Organizations</SectionHeader>
            <TabsPane>
              <Tab title="All">
                <ul>
                  <ListItem count={organizations.total} label="All Organizations" />
                </ul>
              </Tab>
              <Tab title="Labels">
                <LabelsDictionary labels={labels.organizations} />
              </Tab>
            </TabsPane>
          </Section>

          <Section>
            <SectionHeader>Agents</SectionHeader>
            <ul>
              <ListItem count={teams.total} label="All Agents" />
              {teams.items.map(item =>
                  <ListItem key={itemKey++} count={item.count} label={teamNames[item.group]} />)}
            </ul>
          </Section>
        </SectionsPane>
      </NavFrame>
    );
  }


}
