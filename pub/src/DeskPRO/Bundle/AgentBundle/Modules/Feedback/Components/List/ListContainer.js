import React, {Component, PropTypes} from 'react';
import { ListFrame, ControlBar, ListTableViewSwitcher, OrderBy, TableView, TableBody, Pagination }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackCard } from './FeedbackCard';
import { TableHeader } from './TableHeader';
import { FilterBy } from './FilterBy';
import { Row } from './Row';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import * as actions from '../../Actions/FeedbackListActions'
import $ from "jquery";

import { connect } from 'redux/react';
@connect(state => state.FeedbackList)

export class ListContainer extends Component {

  constructor(props) {
    super(props);
    const { query, sort, order, filters, dispatch } = this.props;
    dispatch(actions.loadFeedbackList(query, sort, order, filters));
    dispatch(actions.getFilterValues(filters.alias));
  }

  static propTypes = {
    feedback: PropTypes.array.isRequired,
    sortOptions: PropTypes.array.isRequired,
    sort: PropTypes.string.isRequired,
    sortName: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    filters: PropTypes.object.isRequired,
    query: PropTypes.object.isRequired,
    viewMode: PropTypes.string.isRequired
  };


  render() {
    const displayFields = [
      {name: 'id', label: 'ID'},
      {name: 'status', label: 'Status'},
      {name: 'hidden_status', label: 'Hidden status'},
      {name: 'status_category', label: 'Status category'},
      {name: 'title', label: 'Status category'},
      {name: 'author_name', label: 'Submitter'},
      {name: 'language_id', label: 'Lang'},
      {name: 'type', label: 'Type'},
      {name: 'slug', label: 'Slug'},
      {name: 'date_created', label: 'Created'},
      {name: 'date_published', label: 'Published'},
      {name: 'view_count', label: 'Views'},
      {name: 'total_rating', label: 'Rating'},
      {name: 'num_rating', label: 'Votes'},
      {name: 'num_comments', label: 'Comments'},
      {name: 'validating', label: 'Validating'},
      {name: 'popularity', label: 'Popularity'},
      {name: 'content', label: 'Content'},
      {name: 'custom_category', label: 'Category'}
    ];

    const { feedback, viewMode, sort, sortName, order, filters, query, sortOptions, dispatch } = this.props;

    return (
      <ListFrame>
        <ControlBar>
          <OrderBy sort={sort} sortName={sortName} order={order} sortOptions={sortOptions}
                   toggleOrder={this.toggleOrder.bind(this)}
                   toggleSort={this.toggleSort.bind(this)}
            />
          <FilterBy filters={filters} query={query}/>
          <ListTableViewSwitcher displayFields={displayFields} viewMode={viewMode} dispatch={dispatch}/>
        </ControlBar>

        {this.renderElements(viewMode, feedback)}

        <Pagination/>
      </ListFrame>
    );
  }

  sortTable(param, order) {
    const {dispatch, query, filters} = this.props;
    dispatch(actions.setTableSort(query, param, order, filters));
  }

  /** Change sort option (Order By ...)*/
  toggleSort(newSort, newSortName) {
    const {dispatch, order, query, filters} = this.props;
    dispatch(actions.toggleSort(query, newSort, newSortName, order, filters));
  }

  toggleOrder() {
    const {dispatch, query, sort, order, filters} = this.props;
    dispatch(actions.toggleOrder(query, sort, order, filters));
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
        {elements.map((element, index) => <FeedbackCard key={index} feedback={element}/>)}
      </div>
    );
  }

  renderTableView(elements) {
    return (
      <div>
        <h1>Table View</h1>
        <TableView>
          <TableHeader sortTable={this.sortTable.bind(this)}/>
          <TableBody>
            {elements.map((element, index) => <Row key={index} feedback={element}/>)}
          </TableBody>
        </TableView>
      </div>
    );
  }

}
