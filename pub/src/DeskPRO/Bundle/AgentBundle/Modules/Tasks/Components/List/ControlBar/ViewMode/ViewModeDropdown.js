import React from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';

export class ViewModeDropdown extends React.Component {

  render() {
    return (
      <Menu>
        <Item label="Card View"
              icon="list"/>
        <Item label="Table View"
              icon="table"/>
        <Item label="Kanban"
              icon="sticky-note-o"/>
        <Item label="Calendar"
              icon="calendar"/>
      </Menu>
    );
  }
}
