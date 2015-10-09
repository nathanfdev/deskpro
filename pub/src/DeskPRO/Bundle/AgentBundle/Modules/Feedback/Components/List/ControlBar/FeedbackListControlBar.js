import React from 'react';
import ListFrameMenu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';
import { OrderByContainer } from './OrderByContainer';
import { FilterContainer } from './FilterContainer';
import { ViewSwitcherContainer } from './ViewSwitcherContainer';
import { MassActionCheckboxContainer } from './MassActionCheckboxContainer';

export class FeedbackListControlBar extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      orderByDropdownIsExpanded: false,
      filterByDropdownIsExpanded: false,
      viewModeDropdownIsExpanded: false,
      dropdownOffset: {left: 0, top: 0}
    };
  }

  toggleOrderByDropdown = () => {
    this.setState({
      orderByDropdownIsExpanded: !this.state.orderByDropdownIsExpanded,
      viewModeDropdownIsExpanded: false,
      filterByDropdownIsExpanded: false
    });
  };

  toggleFilterByDropdown = (offset) => {
    this.setState({
      filterByDropdownIsExpanded: !this.state.filterByDropdownIsExpanded,
      orderByDropdownIsExpanded: false,
      viewModeDropdownIsExpanded: false,
      dropdownOffset: offset
    });
  };

  toggleViewModeDropdown = () => {
    this.setState({
      viewModeDropdownIsExpanded: !this.state.viewModeDropdownIsExpanded,
      orderByDropdownIsExpanded: false,
      filterByDropdownIsExpanded: false
    });
  };

  render() {
    return (
      <ListFrameMenu>
        <MassActionCheckboxContainer/>
        <OrderByContainer
          expanded={this.state.orderByDropdownIsExpanded}
          toggleDropdown={this.toggleOrderByDropdown}
          />
        <li>
          <hr/>
        </li>
        <FilterContainer
          expanded={this.state.filterByDropdownIsExpanded}
          toggleDropdown={this.toggleFilterByDropdown}
          offset={this.state.dropdownOffset}
          />
        <li>
          <hr/>
        </li>
        <ViewSwitcherContainer
          expanded={this.state.viewModeDropdownIsExpanded}
          toggleDropdown={this.toggleViewModeDropdown}
          />
      </ListFrameMenu>
    );
  }
}