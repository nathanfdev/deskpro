import React, {Component, PropTypes} from 'react';
import $ from "jquery";

export class FilterBy extends Component {

  render() {
    const {filters}=this.props;
    return (
      <div className="control-button">
        <span className="title">Filter by:</span>
        <a href="#">
          <span className="multi" onClick={this.showFilterChoice.bind(this)}>
            <span className="filter-name" data-filter={filters.alias}>{filters.name}</span>
            <span className="multi-down"><i className="fa fa-caret-down"/></span>
          </span>
        </a>
        <span className="down" onClick={this.showFilterValueChoice.bind(this)}>
          {filters.value ? filters.value : 'select...'} <i className="fa fa-caret-down"/>
        </span>
        {this.props.children}
      </div>
    );
  }


  showFilterChoice(event) {
    event.preventDefault();
    event.stopPropagation();
    let elem         = $(event.target),
        filterChoice = elem.closest('a.ticket-control-button').find('div.filter-choice');
    $('div.dropdown-choice').hide();
    filterChoice.show();
  }

  showFilterValueChoice(event) {
    event.preventDefault();
    event.stopPropagation();
    let elem              = $(event.target),
        filterValueChoice = elem.closest('a.ticket-control-button').find('div.filter-values');
    $('div.dropdown-choice').hide();
    filterValueChoice.show();
  }
}
