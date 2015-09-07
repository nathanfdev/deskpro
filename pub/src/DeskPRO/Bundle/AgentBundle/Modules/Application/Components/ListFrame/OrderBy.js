import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'


export class OrderBy extends Component {

  static propTypes = {
    sortOptions: PropTypes.array.isRequired,
    sort: PropTypes.object.isRequired,
    showSortChoice: PropTypes.func.isRequired,
    toggleSort: PropTypes.func.isRequired,
    toggleOrder: PropTypes.func.isRequired
  };

  render() {
    const {sort,  showSortChoice, toggleSort, sortOptions, toggleOrder} = this.props;
    return (
      <a href="#" className="ticket-control-button">
        <span className="title">Order by:</span>
                <span className="multi" onClick={showSortChoice.bind(this)}>
                    <span className="sort-name">{sort.name}</span>
                    <span className="multi-down"><i className="fa fa-caret-down"/></span>
                </span>
        <OrderSwitcher sort={sort} toggleOrder={toggleOrder.bind(this)}/>
        <OrderByDropdown sortOptions={sortOptions} toggleSort={toggleSort.bind(this)}/>
      </a>
    );
  }
}

export class OrderSwitcher extends React.Component {

  static propTypes = {
    sort: PropTypes.object.isRequired,
    toggleOrder: PropTypes.func.isRequired
  };

  render() {
    const {sort, toggleOrder} = this.props;
    return (
      <span>
        <span onClick={toggleOrder.bind(this)}>
          {sort.order.charAt(0).toUpperCase() + sort.order.slice(1)}
        </span>
        <i className={sort.order === constants.ORDER_DESC ? "fa fa-caret-down" : "fa fa-caret-up" }/>
      </span>
    );
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
              <li key={index} onClick={toggleSort.bind(this)} data-field={option.field}>{option.label}</li>
          )}
        </ul>
      </div>
    );
  }
}
