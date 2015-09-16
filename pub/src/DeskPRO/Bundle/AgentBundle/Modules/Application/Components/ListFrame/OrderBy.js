import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ControlBar';

export class OrderBy extends Component {

  static propTypes = {
    sortOptions: PropTypes.array.isRequired,
    order: PropTypes.string.isRequired
  };

  render() {
    const { order, sortOptions } = this.props;
    let currentSortMode = sortOptions.find((option)=>option.current === true);
    let currentMode     = `${currentSortMode.label} (${order})`;
    return (
      <li>
        <ControlButton title="Order by:" icon={currentSortMode.icon} dropdownClass="order-dropdown"
                       currentMode={currentMode}/>
      </li>
    );
  }

}
