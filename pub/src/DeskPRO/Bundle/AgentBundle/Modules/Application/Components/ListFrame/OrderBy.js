import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import $ from "jquery";
import classNames from 'classnames';
import { DropdownMenu, Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/DropdownMenu';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ControlBar';

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
        <ControlButton title={sortName}/>
        <DropdownMenu>
          {sortOptions.map((option, index)=>
              <Option key={index} active={sort === option.field} onClick={this.handleClick.bind(this, option)}
                      option={option}/>
          )}
          <DropdownMenuFooter>
            <OrderSwitcher order={order} toggleOrder={toggleOrder.bind(this)}/>
          </DropdownMenuFooter>
        </DropdownMenu>
      </div>
    );
  }

  /** Change sort option (Order By ...)*/
  handleClick(newSort, event) {
    event.preventDefault();
    event.stopPropagation();
    const {toggleSort} = this.props;
    var elem          = $(event.target),
          newSortName = elem.text(),
          table       = $('.dpmw--items-table-list').find('table');
    table.find('i.fa').remove();
    elem.closest('.control-button').find('span.control-button-title').text(newSortName);
    toggleSort(newSort.field, newSort.label);
  }
}

export class OrderSwitcher extends Component {

  static propTypes = {
    order: PropTypes.string.isRequired,
    toggleOrder: PropTypes.func.isRequired
  };

  render() {
    const {order, toggleOrder} = this.props;
    return (
      <div className="dpw-navigation-dropdown-options-ordering">
        <span>Sort type:</span>
        <Radio type="asc" order={order} toggleOrder={toggleOrder}/>
        <Radio type="desc" order={order} toggleOrder={toggleOrder}/>
      </div>
    );
  }

}

export class Radio extends Component {
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
    toggleOrder(type);
  }
}
