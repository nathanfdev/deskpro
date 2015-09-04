import React from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'


export class OrderBy extends React.Component {
  render() {
    const {sort, sortName,  showOrderChoice, switchSortDirection, options, orderSwitch} = this.props;
    return (
      <a href="#" className="ticket-control-button">
        <span className="title">Order by:</span>
                <span className="multi" onClick={showOrderChoice.bind(this)}>
                    <span className="sort-name">{sortName}</span>
                    <span className="multi-down"><i className="fa fa-caret-down"/></span>
                </span>
        <OrderSwitcher sort={sort} switchSortDirection={switchSortDirection}/>
        <OrderByDropdown options={options} orderSwitch={orderSwitch.bind(this)}/>
      </a>
    );
  }
}

export class OrderSwitcher extends React.Component {
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

  render() {
    const {options, orderSwitch} = this.props;
    return (
      <div className="focus-choice dropdown-choice">
        <ul>
          {options.map((option, index)=>
              <li onClick={orderSwitch.bind(this)} data-field={option.field}>{option.label}</li>
          )}
        </ul>
      </div>
    );
  }
}
