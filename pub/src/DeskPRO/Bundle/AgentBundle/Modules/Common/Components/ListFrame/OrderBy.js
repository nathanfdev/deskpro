import React, {Component, PropTypes} from 'react';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';

export class OrderBy extends Component {

  static propTypes = {
    currentSortOption: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    children: PropTypes.any.isRequired,
    order: PropTypes.string.isRequired
  };

  render() {
    const { order, currentSortOption, toggleDropdown } = this.props;
    const label = `${currentSortOption.label} (${order})`;
    return (
      <li>
        <ControlButton
          title="Order by:"
          icon={currentSortOption.icon}
          label={label}
          toggleDropdown={toggleDropdown}
          />
        {this.props.children}
      </li>
    );
  }

}
