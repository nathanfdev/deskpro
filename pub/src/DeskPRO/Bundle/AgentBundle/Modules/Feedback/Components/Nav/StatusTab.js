import React, { Component, PropTypes } from 'react';
import { ListItem }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { NestedList } from './NestedList'

export class StatusTab extends React.Component {

  render() {
    const { statuses, onClick } = this.props;
    return (
      <ul>
        <div onClick={onClick.bind(this, {'status':'new'})}>
          <ListItem count={statuses.new} label="New"/>
        </div>
        <NestedList node={statuses.active} onClick={onClick.bind(this)} status="active" label="Active"/>
        <NestedList node={statuses.closed} onClick={onClick.bind(this)} status="closed" label="Closed"/>
        <NestedList node={statuses.hidden} onClick={onClick.bind(this)} status="hidden" label="Hidden"/>
      </ul>
    );
  }
}