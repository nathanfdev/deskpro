import React from 'react';
import Formsy from 'formsy-react';
import FRC from '../../../../../Component/FormComponents/main.js';
import * as constants from '../../../Constants/Constants';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';

const TaskOrderHover = React.createClass({

  mixins: [
    require('react-onclickoutside')
  ],

  getInitialState: function getInitialState() {
    return {
      filterDates: {}
    };
  },

  setOrder: function setOrder(order) {
    this.props.applyOrder(order);
  },

  handleClickOutside: function handleClickOutside() {
    this.props.closeWindow();
  },

  render: function render() {
    const order = this.props.order;

    return (<Menu>
      <Item onClick={this.setOrder.bind(this, {order: 'list'})}
            isActive={order === 'list'}
            checked={order === 'list'}
            label="List"
            icon="list"/>
      <Item onClick={this.setOrder.bind(this, {order: 'project'})}
            isActive={order === 'project'}
            checked={order === 'project'}
            label="Project"
            icon="briefcase"/>
      <Item onClick={this.setOrder.bind(this, {order: 'date_due'})}
            isActive={order === 'date_due'}
            checked={order === 'date_due'}
            label="Due Date"
            icon="calendar"/>
      <Item onClick={this.setOrder.bind(this, {order: 'date_done'})}
            isActive={order === 'date_done'}
            checked={order === 'date_done'}
            label="Done Date"
            icon="calendar"/>
      <Item onClick={this.setOrder.bind(this, {order: 'date_created'})}
            isActive={order === 'date_created'}
            checked={order === 'date_created'}
            label="Created Date"
            icon="calendar"/>
      <Item onClick={this.setOrder.bind(this, {order: 'assignee'})}
            isActive={order === 'assignee'}
            checked={order === 'assignee'}
            label="Assignee"
            icon="user"/>
      <MenuFooter>
        <MenuFooterOptions options={[{id: 'asc', onClick: this.setOrder.bind(this, {direction: 'asc'}), label: 'Asc'},
                                             {id: 'desc', onClick: this.setOrder.bind(this, {direction: 'desc'}), label: 'Desc'}
                                            ]} active={this.props.direction}>
          Sort
        </MenuFooterOptions>
      </MenuFooter>
    </Menu>);
  }
});

module.exports = TaskOrderHover;
