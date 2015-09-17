import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';

import { List }
  from './List';

import * as actions from '../../Actions/crmListActions'
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import $ from "jquery";

@connect(state => state.CrmNav)

export class ListContainer extends Component {

  render() {

    return (
      <List {...this.props} />
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
