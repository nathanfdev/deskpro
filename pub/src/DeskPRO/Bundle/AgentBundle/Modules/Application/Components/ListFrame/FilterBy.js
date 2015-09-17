import React, {Component, PropTypes} from 'react';
import $ from "jquery";

export class FilterBy extends Component {

  render() {
    const {filters}=this.props;
    return (
      <li>
        <a href="#" className="dpwd-navigation-dropdown-top-row-button">
          <span className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">Filter by:</span>
          <span className="dpwd-navigation-dropdown-top-row-button-icon"><i className="fa fa-calendar-o"></i></span>
          <span className="dpwd-navigation-dropdown-top-row-button-text">{filters.name} ({filters.value ? filters.value : '...'})</span>
          <span className="dpwd-navigation-dropdown-top-row-button-icon"><i className="fa fa-caret-down"></i></span>
        </a>
        {this.props.children}
      </li>
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
