import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import $ from "jquery";

export class OrderBy extends Component {

  static propTypes = {
    sortOptions: PropTypes.array.isRequired,
    sort: PropTypes.string.isRequired,
    sortName: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    toggleSort: PropTypes.func.isRequired,
    toggleOrder: PropTypes.func.isRequired
  };

  render() {
    const { sortName, order, toggleSort, sortOptions, toggleOrder, dispatch} = this.props;

    return (
      <a href="#" className="ticket-control-button">
        <span className="title">Order by:</span>
                <span className="multi" onClick={this.handleClick.bind(this)}>
                    <span className="sort-name">{sortName}</span>
                    <span className="multi-down"><i className="fa fa-caret-down"/></span>
                </span>
        <OrderSwitcher order={order} toggleOrder={toggleOrder.bind(this)} dispatch={dispatch}/>
        <OrderByDropdown sortOptions={sortOptions} toggleSort={toggleSort.bind(this)}/>
      </a>
    );
  }


  handleClick(event) {
    event.preventDefault();
    event.stopPropagation();
    let elem = $(event.target),
      filterChoice = elem.closest('a.ticket-control-button').find('div.focus-choice');
    $('div.dropdown-choice').hide();
    filterChoice.show();
  }

}

export class OrderSwitcher extends React.Component {

  static propTypes = {
    order: PropTypes.string.isRequired,
    toggleOrder: PropTypes.func.isRequired
  };

  render() {
    const {order, toggleOrder} = this.props;

    return (
      <span className="order-switcher">
        <span onClick={this.handleClick.bind(this, toggleOrder)}>
          {order.charAt(0).toUpperCase() + order.slice(1)}
        </span>
        <i className={order === constants.ORDER_DESC ? "fa fa-caret-down" : "fa fa-caret-up" }/>
      </span>
    );
  }

  /** Change sort order (ASC, DESC)*/
  handleClick(toggleOrder, event) {
    event.preventDefault();
    event.stopPropagation();
    $('div.dropdown-choice').hide();
    let elem = $(event.target);
    elem.closest('span.order-switcher').find('i.fa').toggleClass('fa-caret-up').toggleClass('fa-caret-down');
    toggleOrder.call();
  }

}

export class OrderByDropdown extends React.Component {
  static propTypes = {
    sortOptions: PropTypes.array.isRequired,
    toggleSort: PropTypes.func.isRequired
  };

  render() {
    const {sortOptions, toggleSort} = this.props;
    return (
      <div className="focus-choice dropdown-choice">
        <ul>
          {sortOptions.map((option, index)=>
              <li key={index} onClick={this.handleClick.bind(this,toggleSort)}
                  data-field={option.field}>{option.label}</li>
          )}
        </ul>
      </div>
    );
  }

  /** Change sort option (Order By ...)*/
  handleClick(toggleSort, event) {
    event.preventDefault();
    event.stopPropagation();
    var elem = $(event.target),
      newSortName = elem.text(),
      table = elem.closest('div.feedback-list').find('table'),
      newSort = elem.data('field');
    table.find('i.fa').remove();
    elem.closest('a.ticket-control-button').find('span.sort-name').text(newSortName);
    $('div.dropdown-choice').hide();
    toggleSort(newSort, newSortName);
  }

}
