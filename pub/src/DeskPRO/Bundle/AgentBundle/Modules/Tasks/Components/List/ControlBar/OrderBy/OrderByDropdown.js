import React from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';

export class OrderByDropdown extends React.Component {

  render() {
    return (
      <Menu>
        <Item label="List"
              icon="list"/>
        <Item label="Project"
              icon="briefcase"/>
        <Item label="Due Date"
              icon="calendar"/>
        <Item label="Done Date"
              icon="calendar"/>
        <Item label="Created Date"
              icon="calendar"/>
        <Item label="Assignee"
              icon="user"/>
        <MenuFooter>
          <MenuFooterOptions>
            Sort
          </MenuFooterOptions>
        </MenuFooter>
      </Menu>
    );
  }
}
