import React from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';

export default class TaskControlsViewSwitcher extends React.Component {
  render() {
    return (<Menu>
              <Item onClick={this.props.setView.bind(this, 'list')}
                isActive={this.props.view === 'list'}
                checked={this.props.view === 'list'}
                icon="list">List</Item>
              <Item onClick={this.props.setView.bind(this, 'condensed')}
                isActive={this.props.view === 'condensed'}
                checked={this.props.view === 'condensed'}
                icon="table">Condensed</Item>
              <Item onClick={this.props.setView.bind(this, 'kanban')}
                isActive={this.props.view === 'kanban'}
                checked={this.props.view === 'kanban'}
                icon="sticky-note-o">Kanban</Item>
              <Item onClick={this.props.setView.bind(this, 'calendar')}
                isActive={this.props.view === 'calendar'}
                checked={this.props.view === 'calendar'}
                icon="calendar">Calendar</Item>
            </Menu>);
  }
}
