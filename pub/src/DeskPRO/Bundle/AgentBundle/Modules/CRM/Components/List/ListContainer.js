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
    displayFields: PropTypes.array.isRequired,
    sort: PropTypes.string.isRequired,
    sortName: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    filters: PropTypes.object.isRequired,
    query: PropTypes.object.isRequired,
    viewMode: PropTypes.string.isRequired,
    sortTable: PropTypes.func.isRequired,
    toggleOrder: PropTypes.func.isRequired,
    toggleSort: PropTypes.func.isRequired
  };

  render() {

    const displayFields = [];

    const {
      viewMode, sortTable, sort, sortName, order, filters, query, sortOptions
      } = this.props;

    return (
      <ListFrame>
        <ControlBar>
          <OrderBy sort={sort} sortName={sortName} order={order} sortOptions={sortOptions}
                   toggleOrder={this.toggleOrder.bind(this)}
                   toggleSort={this.toggleSort.bind(this)}
            />
          <ListTableViewSwitcher displayFields={displayFields} {...this.props}/>
        </ControlBar>
      </ListFrame>
    );
  }


  /** Change sort option (Order By ...)*/
  toggleSort(event) {
    event.preventDefault();
    event.stopPropagation();
    const {dispatch, order, query, filters} = this.props;
    var elem = $(event.target),
      newSort = elem.data('field');
    this.setState({sort: newSort});
    elem.closest('div.feedback-list').find('table').find('i.fa').remove();
    elem.closest('a.ticket-control-button').find('span.sort-name').text(elem.text());
    $('div.dropdown-choice').hide();
    /* @ToDo dispatch(actions.___loadList___(query, newSort, order, filters));*/
  }

  /** Change sort order (ASC, DESC)*/
  toggleOrder(event) {
    event.preventDefault();
    event.stopPropagation();
    const {dispatch, sort, order, query, filters} = this.props;
    $('div.dropdown-choice').hide();
    let elem = $(event.target),
      newOrder = (order === constants.ORDER_ASC) ? constants.ORDER_DESC : constants.ORDER_ASC;
    elem.closest('a.ticket-control-button').find('i.fa').toggleClass('fa-caret-up').toggleClass('fa-caret-down');
    dispatch(AppActions.toggleOrder());
    // @ToDo dispatch(actions.___loadList___(query, sort, newOrder, filters));
  }
}
