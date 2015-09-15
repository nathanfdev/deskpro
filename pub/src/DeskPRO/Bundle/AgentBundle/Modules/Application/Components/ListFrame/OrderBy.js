import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import $ from "jquery";
import classNames from 'classnames';

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
    const { sortName, sort, order, toggleSort, sortOptions, toggleOrder} = this.props;

    return (
      <div className="control-button">

        <span className="title">Order by:</span>
        <a href="#">
          <span className="multi" onClick={this.handleClick.bind(this)}>
            <span className="sort-name">{sortName}</span>
            <span className="multi-down"><i className="fa fa-caret-down"/></span>
          </span>
        </a>
        <OrderByDropdown order={order} toggleOrder={toggleOrder.bind(this)} sortOptions={sortOptions} sort={sort}
                         toggleSort={toggleSort.bind(this)}/>
      </div>
    );
  }


  handleClick(event) {
    event.preventDefault();
    event.stopPropagation();
    let elem     = $(event.target),
        dropdown = elem.closest('.control-button').find('.dpw-navigation-dropdown');
    dropdown.toggle();
  }

}

export class OrderByDropdown extends React.Component {
  static propTypes = {
    sort: PropTypes.string.isRequired,
    sortOptions: PropTypes.array.isRequired,
    toggleSort: PropTypes.func.isRequired
  };

  render() {
    const {sortOptions, sort, order, toggleSort, toggleOrder} = this.props;
    return (
      <div className="dpw-navigation-dropdown">
        <ul>
          {sortOptions.map((option, index)=>
              <Option key={index} sort={sort} toggleSort={toggleSort} option={option}/>
          )}
          <OrderSwitcher order={order} toggleOrder={toggleOrder.bind(this)}/>
        </ul>
      </div>
    );
  }


}

export class Option extends React.Component {
  static propTypes = {
    toggleSort: PropTypes.func.isRequired
  };

  render() {
    const {option, sort, toggleSort} = this.props;
    var classes = classNames('dpw-navigation-dropdown-item', {
      'active': option.field === sort
    });
    return (
      <li>
        <a href="#" className={classes} onClick={this.handleClick.bind(this, toggleSort, option.field)}>
              <span className="dpw-navigation-dropdown-item-mark">
                <span className="dpw-navigation-dropdown-item-icon"><i className="fa fa-calendar-o"></i></span>
              </span>
          <span className="dpw-navigation-dropdown-item-title">{option.label}</span>
          {option.field === sort ?
           <span className="dpw-navigation-dropdown-item-status"><i className="fa fa-check"></i></span>
            : ''
          }
        </a>
      </li>
    );
  }

  /** Change sort option (Order By ...)*/
  handleClick(toggleSort, newSort, event) {
    event.preventDefault();
    event.stopPropagation();
    var elem        = $(event.target),
        newSortName = elem.text(),
        table       = elem.closest('div.feedback-list').find('table');
    table.find('i.fa').remove();
    elem.closest('.control-button').find('span.sort-name').text(newSortName);
    $('.dpw-navigation-dropdown').hide();
    toggleSort(newSort, newSortName);
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
      <li>
        <div className="dpw-navigation-dropdown-item dpw-navigation-dropdown-footer">
          <div className="dpw-navigation-dropdown-options-ordering">
            <span>Sort type:</span>
            <Radio type="asc" order={order} toggleOrder={toggleOrder}/>
            <Radio type="desc" order={order}  toggleOrder={toggleOrder}/>
          </div>
        </div>
      </li>
    );
  }

}

export class Radio extends React.Component {
  render() {
    const {type, order, toggleOrder}=this.props;
    var classes = classNames('dpwd-radio-button', {
      'active': type === order
    });
    return (
      <span className={classes} onClick={this.handleClick.bind(this, toggleOrder, type)}>
        <span className="dpwd-radio-button-disc"></span>
        <span className="radio-button-title">{type.charAt(0).toUpperCase() + type.slice(1)}</span>
      </span>
    );
  }
  /** Change sort order (ASC, DESC)*/
  handleClick(toggleOrder, type, event) {
    event.preventDefault();
    event.stopPropagation();
    $('div.dropdown-choice').hide();
    let elem = $(event.target);
    elem.closest('span.order-switcher').find('i.fa').toggleClass('fa-caret-up').toggleClass('fa-caret-down');
    toggleOrder(type);
  }
}
