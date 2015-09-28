import React from 'react';
import Formsy from 'formsy-react';
import FRC from '../../../../../Component/FormComponents/main.js';
import * as constants from '../../../Constants/Constants';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';

import * as TaskActions from '../Actions/TaskListActions';

const TaskOrderHover = React.createClass({

  mixins: [
    require('react-onclickoutside')
  ],

  getInitialState: function() {
    return {
      filterDates: {}
    };
  },

  handleClickOutside: function(evt) {
    this.props.closeWindow();
  },

  componentDidMount: function() {

  },

  setOrder: function(order) {
    this.props.applyOrder(order);
  },

  render: function() {
    const order = this.props.order;

    return (<Menu>
              <Item onClick={this.setOrder.bind(this, {order: 'list'})}
                isActive={order === 'list'}
                checked={order === 'list'}
                icon="list">List</Item>
              <Item onClick={this.setOrder.bind(this, {order: 'project'})}
                isActive={order === 'project'}
                checked={order === 'project'}
                icon="briefcase">Project</Item>
              <Item onClick={this.setOrder.bind(this, {order: 'due'})}
                isActive={order === 'due'}
                checked={order === 'due'}
                icon="calendar">Due Date</Item>
              <Item onClick={this.setOrder.bind(this, {order: 'done'})}
                isActive={order === 'done'}
                checked={order === 'done'}
                icon="calendar">Done Date</Item>
              <Item onClick={this.setOrder.bind(this, {order: 'created'})}
                isActive={order === 'created'}
                checked={order === 'created'}
                icon="calendar">Created Date</Item>
              <Item onClick={this.setOrder.bind(this, {order: 'assignee'})}
                isActive={order === 'assignee'}
                checked={order === 'assignee'}
                icon="user">Assignee</Item>
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
