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
    toggleSort: PropTypes.func.isRequired,
    toggleView: PropTypes.func.isRequired
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
          <ListTableViewSwitcher displayFields={displayFields} toggleView={this.toggleView.bind(this)} {...this.props}/>
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
     name = elem.text(),
     table = elem.closest('div.feedback-list').find('table'),
     newSort = elem.data('field');
     table.find('i.fa').remove();
     this.setState({sort: newSort});
     elem.closest('a.ticket-control-button').find('span.sort-name').text(name);
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
      newOrder = constants.ORDER_ASC;
    if (order === newOrder) {
      newOrder = constants.ORDER_DESC;
      elem.closest('a.ticket-control-button').find('i.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
    }
    else {
      elem.closest('a.ticket-control-button').find('i.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
    }
    dispatch(AppActions.toggleOrder());
    // @ToDo dispatch(actions.___loadList___(query, sort, newOrder, filters));
  }
}
