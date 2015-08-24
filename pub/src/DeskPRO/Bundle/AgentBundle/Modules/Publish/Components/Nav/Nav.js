import React from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, TabsPane, Tab, ListItem, ButtonsPane, Button }
       from 'DeskPRO/Bundle/Agentbundle/Modules/Application/Components/NavFrame/index';

export class Nav extends React.Component {
  render() {
    return (
      <NavFrame>
        <NavFrameHeader icon="fa-edit">Publish</NavFrameHeader>
        <TabsPane>
          <Tab title="KB">
            <ButtonsPane>
              <Button title="Glossary" icon="fa-quote-left" />
              <Button title="Search" icon="fa-search" />
              <Button title="Comments" icon="fa-comments-o" />
            </ButtonsPane>
          </Tab>
          <Tab title="News"></Tab>
          <Tab title="Downloads"></Tab>
          <Tab title="Todos"></Tab>
        </TabsPane>
      </NavFrame>
    );
  }


}
