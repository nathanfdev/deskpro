import React, {Component, PropTypes} from 'react';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';

export class FilterBy extends Component {

  static propTypes = {
    title: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    children: PropTypes.any,
    toggleDropdown: PropTypes.func.isRequired
  };

  render() {
    const {toggleDropdown, title, label} = this.props;
    return (
      <li>
        <ControlButton
          title={title}
          label={label}
          toggleDropdown={toggleDropdown}
          />
        {this.props.children}
      </li>
    );
  }
}
