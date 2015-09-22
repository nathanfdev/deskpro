import React from 'react';
import { ControlBar, ControlButtonsRow } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ControlBar';
import { OrderByContainer } from './OrderByContainer';
import { FilterContainer } from './FilterContainer';
import { ViewSwitcherContainer } from './ViewSwitcherContainer';
import { OrderByDropdownContainer } from './OrderByDropdownContainer';
import { FilterByDropdownContainer } from './FilterByDropdownContainer';
import { ViewSwitcherDropdownContainer } from './ViewSwitcherDropdownContainer';

export class FeedbackListControlBar extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      orderByDropdownIsExpanded: false,
      filterByDropdownIsExpanded: false,
      viewModeDropdownIsExpanded: false,
      dropdownOffset: {left: 0, top: 0}
    }
  }

  render() {
    return (
      <ControlBar>
        <ControlButtonsRow>
          <OrderByContainer toggleDropdown={this.toggleOrderByDropdown}/>
          <li>
            <hr/>
          </li>
          <FilterContainer toggleDropdown={this.toggleFilterByDropdown}/>
          <li>
            <hr/>
          </li>
          <ViewSwitcherContainer toggleDropdown={this.toggleViewModeDropdown}/>
        </ControlButtonsRow>
        {this.renderOrderByDropdown()}
        {this.renderFilterByDropdown()}
        {this.renderViewModeDropdown()}
      </ControlBar>
    );
  }

  renderOrderByDropdown() {
    if (this.state.orderByDropdownIsExpanded) {
      return (
        <OrderByDropdownContainer offset={this.state.dropdownOffset}/>
      );
    }
  }

  renderFilterByDropdown() {
    if (this.state.filterByDropdownIsExpanded) {
      return (
        <FilterByDropdownContainer offset={this.state.dropdownOffset}/>
      );
    }
  }

  renderViewModeDropdown() {
    if (this.state.viewModeDropdownIsExpanded) {
      return (
        <ViewSwitcherDropdownContainer offset={this.state.dropdownOffset}/>
      );
    }
  }

  toggleOrderByDropdown = (offset) => {
    this.setState({
      orderByDropdownIsExpanded: !this.state.orderByDropdownIsExpanded,
      viewModeDropdownIsExpanded: false,
      filterByDropdownIsExpanded: false,
      dropdownOffset: offset
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

  toggleViewModeDropdown = (offset) => {
    this.setState({
      viewModeDropdownIsExpanded: !this.state.viewModeDropdownIsExpanded,
      orderByDropdownIsExpanded: false,
      filterByDropdownIsExpanded: false,
      dropdownOffset: offset
    });
  };
}