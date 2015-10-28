import React, {Component, PropTypes} from 'react';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';

export class FilterBy extends Component {

  static propTypes = {
    children: PropTypes.any,
    toggleDropdown: PropTypes.func.isRequired
  };

  render() {
    const {toggleDropdown} = this.props;
    return (
      <li>
        <ControlButton
          title="Filter by"
          toggleDropdown={toggleDropdown}
          />
        {this.props.children}
      </li>
    );
  }
}
