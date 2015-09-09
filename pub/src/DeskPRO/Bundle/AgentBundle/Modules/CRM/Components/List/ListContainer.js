import React, {Component, PropTypes} from 'react';
import { connect } from 'redux/react';

import { ListFrame, ControlBar, ListTableViewSwitcher, OrderBy, TableView, TableBody }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

import * as actions from '../../Actions/crmListActions'
import * as AppActions from "DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/AppActions";
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import $ from "jquery";

@connect(state => state.CrmNav)

export class ListContainer extends Component {

  static propTypes = {
    sortOptions: PropTypes.array.isRequired,
    sort: PropTypes.string.isRequired,
    sortName: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    viewMode: PropTypes.string.isRequired,
    sortTable: PropTypes.func.isRequired,
    toggleOrder: PropTypes.func.isRequired,
    toggleSort: PropTypes.func.isRequired
  };

  render() {

    const displayFields = [];

    const { viewMode, sortTable, sort, sortName, order, sortOptions, dispatch } = this.props;

    return (
      <ListFrame>
        <ControlBar>
          <OrderBy sort={sort} sortName={sortName} order={order} sortOptions={sortOptions}
                   toggleOrder={this.toggleOrder.bind(this)}
                   toggleSort={this.toggleSort.bind(this)}
            />
          <ListTableViewSwitcher displayFields={displayFields} viewMode={viewMode} dispatch={dispatch}/>
        </ControlBar>
      </ListFrame>
    );
  }


  /** Change sort option (Order By ...)*/
  toggleSort(newSort, newSortName) {
    console.log('Toggle sort params: ', newSort, newSortName);
    /* @ToDo dispatch(actions.___toggleSort_and_reloadList___(query, newSort, order, filters));*/
  }

  /** Change sort order (ASC, DESC)*/
  toggleOrder() {
    // @ToDo dispatch(actions.___toggleOrder_and_reloadList___(query, sort, newOrder, filters));
  }
}
