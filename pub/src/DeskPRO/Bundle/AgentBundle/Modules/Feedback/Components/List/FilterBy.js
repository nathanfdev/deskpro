import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/FeedbackListActions'
import $ from "jquery";

@connect(state => state.FeedbackList)

export class FilterBy extends React.Component {

  showFilterChoice(event) {
    event.preventDefault();
    event.stopPropagation();
    let elem = $(event.target),
      filterChoice = elem.closest('a.ticket-control-button').find('div.filter-choice');
    $('div.dropdown-choice').hide();
    filterChoice.show();
  }

  showFilterValueChoice(event) {
    event.preventDefault();
    event.stopPropagation();
    let elem = $(event.target),
      filterValueChoice = elem.closest('a.ticket-control-button').find('div.filter-values');
    $('div.dropdown-choice').hide();
    filterValueChoice.show();
  }

  filterChosen(event) {
    event.preventDefault();
    event.stopPropagation();
    const {dispatch,query, sort, filters} = this.props;
    let elem = $(event.target),
      target = elem.closest('a.ticket-control-button').find('span.filter-name'),
      filterName = elem.text(),
      filterAlias = elem.data('filter');
    target.text(filterName);
    target.data('filter', filterAlias);
    dispatch(actions.resetFilters(filterAlias, filterName));
    dispatch(actions.getFilterValues(filterAlias));
    dispatch(actions.loadFeedbackList(query, sort, filters));
    $('div.dropdown-choice').hide();
  }

  filterValueChosen(event) {
    event.preventDefault();
    event.stopPropagation();
    const {dispatch, query, filters, sort} = this.props;
    let elem = $(event.target),
      value = elem.text(),
      filter = elem.closest('a.ticket-control-button').find('span.filter-name').data('filter');
    dispatch(actions.setFilterValue(filter, value));
    dispatch(actions.loadFeedbackList(query, sort, filters));
    $('div.dropdown-choice').hide();
  }

  resetFilterValue(event) {
    event.preventDefault();
    event.stopPropagation();
    const {dispatch, query, filters, sort} = this.props;
    dispatch(actions.setFilterValue());
    dispatch(actions.loadFeedbackList(query, sort, filters));
    $('div.dropdown-choice').hide();
  }

  render() {
    const {query, filterValues, filters} = this.props;
    return (
      <a href="#" className="ticket-control-button">
        <span className="title">Filter by:</span>
                    <span className="multi" onClick={this.showFilterChoice.bind(this)}>
                        <span className="filter-name" data-filter={filters.alias}>{filters.name}</span>
                        <span className="multi-down"><i className="fa fa-caret-down"/></span>
                    </span>
                <span className="down" onClick={this.showFilterValueChoice.bind(this)}>
                    {filters.value ? filters.value : 'select...'} <i className="fa fa-caret-down"/>
                </span>

        <div className="filter-choice dropdown-choice">
          <ul>
            {query.hasOwnProperty('status') ? '' :
              <li onClick={this.filterChosen.bind(this)} data-filter='status'>Status</li>}
            {query.hasOwnProperty('category') ? '' :
              <li onClick={this.filterChosen.bind(this)} data-filter='category'>Type</li>}
            {query.hasOwnProperty('custom_category') ? '' :
              <li onClick={this.filterChosen.bind(this)} data-filter='custom_category'>Category</li>}
          </ul>
        </div>

        <div className="filter-values dropdown-choice">
          <ul>
            {filters.value ? <li onClick={this.resetFilterValue.bind(this)}>Reset filter</li> : ''}
            {filterValues.map((item, index) => {
              return <li key={index} onClick={this.filterValueChosen.bind(this)}>{item}</li>;
            })}
          </ul>
        </div>

      </a>);
  }
}