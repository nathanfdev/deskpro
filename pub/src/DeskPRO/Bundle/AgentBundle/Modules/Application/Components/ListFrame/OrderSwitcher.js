import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import classNames from 'classnames';

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
      <span className={classes} onClick={this.handleClick.bind(this, type)}>
        <span className="dpwd-radio-button-disc"></span>
        <span className="radio-button-title">{type.charAt(0).toUpperCase() + type.slice(1)}</span>
      </span>
    );
  }

  /** Change sort order (ASC, DESC)*/
  handleClick(type, event) {
    event.preventDefault();
    event.stopPropagation();
    const {toggleOrder} = this.props;
    toggleOrder(type);
  }
}
