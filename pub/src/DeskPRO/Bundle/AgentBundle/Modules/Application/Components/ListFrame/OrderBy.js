import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ControlBar';

export class OrderBy extends Component {

  static propTypes = {
    currentSortMode: PropTypes.object.isRequired,
    order: PropTypes.string.isRequired
  };

  render() {
    const { order, currentSortMode } = this.props;
    let label = `${currentSortMode.label} (${order})`;
    return (
      <li>
        <ControlButton title="Order by:" icon={currentSortMode.icon} dropdownClass="order-dropdown"
                       label={label}/>
      </li>
    );
  }

}
