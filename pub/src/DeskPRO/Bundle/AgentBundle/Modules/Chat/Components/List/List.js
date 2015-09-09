import React from 'react';

import { ListFrame, ControlBar, OrderBy, ListTableViewSwitcher, TableView, TableBody  }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { TableHeader } from './TableHeader';
import { Row } from './Row';
import { ChatCard } from './ChatCard.js';
import * as actions from '../../Actions/chatListActions';
import * as AppActions from "DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/AppActions";
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import $ from "jquery";

import { connect } from 'redux/react';
@connect(state => state.ChatList)

export class List extends React.Component {
  render() {
    const displayFields = [];

    const { elements, viewMode, sort, sortName, order, sortOptions, dispatch } = this.props;

    return (
      <ListFrame>
        <ControlBar>
          <OrderBy sort={sort} sortName={sortName} order={order} sortOptions={sortOptions}
                   toggleSort={this.toggleSort.bind(this)}
                   toggleOrder={this.toggleOrder.bind(this)}
            />
          <ListTableViewSwitcher displayFields={displayFields} viewMode={viewMode} dispatch={dispatch}/>
        </ControlBar>
        {viewMode === constants.VIEW_MODE_LIST ?
          this.renderListView(elements) :
          this.renderTableView(elements)
        }
      </ListFrame>
    );
  }

  renderElements(view, elements) {
    if (!elements.length) {
      return 'No data to display'
    }

    switch (view) {
      case 'list':
        return this.renderListView(elements);
      case 'table':
        return this.renderTableView(elements);
      default:
        throw `Unknown "${view}" view type`;
    }
  }

  renderListView(elements) {
    return (
      <div>
        <h1>List View</h1>
        {elements.map((element, index) => <ChatCard key={index} chat={element}/>)}
      </div>
    );
  }

  renderTableView(elements) {
    return (
      <div>
        <h1>Table View</h1>
        <TableView>
          <TableHeader/>
          <TableBody>
            {elements.map((element, index) => <Row key={index} element={element}/>)}
          </TableBody>
        </TableView>
      </div>
    );
  }

  toggleOrder() {
    const {dispatch} = this.props;
    dispatch(AppActions.toggleOrder());
    //dispatch(actions.load(filters));
  }

  toggleSort(sort) {
    return (e) => {
      e.preventDefault();
      this.props.dispatch(actions.toggleSort(sort));
    }
  }
}
