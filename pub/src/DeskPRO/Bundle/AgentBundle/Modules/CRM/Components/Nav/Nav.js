import React from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, TabPane, Tab, ListItem }
       from 'DeskPRO/Bundle/Agentbundle/Modules/Application/Components/NavFrame/index';

export class Nav extends React.Component {
  render() {
    return (
      <NavFrame>
        <NavFrameHeader icon="fa-users">CRM</NavFrameHeader>
        <SectionsPane>
          <Section>
            <SectionHeader>People</SectionHeader>
            <TabPane>
              <Tab title="Groups">
                <ul>
                  <ListItem count="25" label="Everyone" />
                  <ListItem count="13" label="Some groups" />
                </ul>
              </Tab>
              <Tab title="Filters">Filters tab content</Tab>
              <Tab title="Labels">Labels tab content</Tab>
            </TabPane>
          </Section>

          <Section>
            <SectionHeader>Organizations</SectionHeader>
            <TabPane>
              <Tab title="All">
                <ul>
                  <ListItem count="42" label="All Organizations" />
                </ul>
              </Tab>
              <Tab title="Labels">Labels tab content</Tab>
            </TabPane>
          </Section>

          <Section>
            <SectionHeader>Agents</SectionHeader>
            <ul>
              <ListItem count="27" label="All Agents" />
              <ListItem count="18" label="Team A" />
              <ListItem count="9" label="Team B" />
            </ul>
          </Section>
        </SectionsPane>
      </NavFrame>
    );
  }
}
