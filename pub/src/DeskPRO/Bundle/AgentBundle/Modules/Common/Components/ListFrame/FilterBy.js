import React, {Component, PropTypes} from 'react';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';

export class FilterBy extends Component {

  static propTypes = {
    title: PropTypes.string.isRequired,
    filterLabel: PropTypes.string.isRequired,
    filtersCounter: PropTypes.number.isRequired,
    children: PropTypes.any,
    toggleDropdown: PropTypes.func.isRequired
  };

  render() {
    const {toggleDropdown, filtersCounter, filterLabel} = this.props;
    const title = filtersCounter > 0 ? 'Filter By:' : 'Filter By';
    let label = '';
    if (filtersCounter === 1) {
      label = filterLabel;
    } else if (filtersCounter > 1) {
      label = filtersCounter + ' Options';
    }
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
