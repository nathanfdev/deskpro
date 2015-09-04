import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'


export class OrderBy extends Component {

  static propTypes = {
    sortOptions: PropTypes.array.isRequired,
    sort: PropTypes.object.isRequired,
    sortName: PropTypes.string.isRequired,
    showOrderChoice: PropTypes.func.isRequired,
    switchSortDirection: PropTypes.func.isRequired,
    switchOrder: PropTypes.func.isRequired
  };

  render() {
    const {sort, sortName,  showOrderChoice, switchSortDirection, sortOptions, switchOrder} = this.props;
    return (
      <a href="#" className="ticket-control-button">
        <span className="title">Order by:</span>
                <span className="multi" onClick={showOrderChoice.bind(this)}>
                    <span className="sort-name">{sortName}</span>
                    <span className="multi-down"><i className="fa fa-caret-down"/></span>
                </span>
        <OrderSwitcher sort={sort} switchSortDirection={switchSortDirection.bind(this)}/>
        <OrderByDropdown sortOptions={sortOptions} switchOrder={switchOrder.bind(this)}/>
      </a>
    );
  }
}

export class OrderSwitcher extends React.Component {

  static propTypes = {
    sort: PropTypes.object.isRequired,
    switchSortDirection: PropTypes.func.isRequired
  };

  render() {
    const {sort, switchSortDirection} = this.props;
    return (
      <span>
        <span onClick={switchSortDirection.bind(this)}>
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
    switchOrder: PropTypes.func.isRequired
  };

  render() {
    const {sortOptions, switchOrder} = this.props;
    return (
      <div className="focus-choice dropdown-choice">
        <ul>
          {sortOptions.map((option, index)=>
              <li key={index} onClick={switchOrder.bind(this)} data-field={option.field}>{option.label}</li>
          )}
        </ul>
      </div>
    );
  }
}
