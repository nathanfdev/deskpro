import React, {Component, PropTypes} from 'react';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';

export class OrderBy extends Component {

  static propTypes = {
    currentSortMode: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    children: PropTypes.any.isRequired,
    order: PropTypes.string.isRequired
  };

  render() {
    const { order, currentSortMode, toggleDropdown } = this.props;
    const label = `${currentSortMode.label} (${order})`;
    return (
      <li>
        <ControlButton
          title="Order by:"
          icon={currentSortMode.icon}
          label={label}
          toggleDropdown={toggleDropdown}
          />
        {this.props.children}
      </li>
    );
  }

}
