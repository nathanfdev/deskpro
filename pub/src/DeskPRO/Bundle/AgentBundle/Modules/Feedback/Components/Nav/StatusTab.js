import React, { Component, PropTypes } from 'react';
import { ListItem }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { NestedList } from './NestedList'

export class StatusTab extends Component {

  static propTypes = {
    statuses: PropTypes.array.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { statuses, onClick, currentGroup } = this.props;
    return (
      <ul>
        <div onClick={onClick.bind(this, {name:'status', value:'new'})}>
          <ListItem count={statuses.new} label="New"
                    active={currentGroup.name === 'status' && currentGroup.value === 'new'}
            />
        </div>
        <NestedList currentGroup={currentGroup} node={statuses.active} onClick={onClick.bind(this)} status="active" label="Active"/>
        <NestedList currentGroup={currentGroup} node={statuses.closed} onClick={onClick.bind(this)} status="closed" label="Closed"/>
        <NestedList currentGroup={currentGroup} node={statuses.hidden} onClick={onClick.bind(this)} status="hidden" label="Hidden"/>
      </ul>
    );
  }
}