import React, {Component, PropTypes} from 'react';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';
import { OrderByContainer } from './OrderByContainer';
import { FilterContainer } from './FilterContainer';
import { ViewSwitcherContainer } from './ViewSwitcherContainer';
import { MassActionCheckboxContainer } from './MassActionCheckboxContainer';

export class FeedbackListControlBar extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

    constructor(props) {
      super(props);
      this.state = {
        orderByDropdownIsExpanded: false,
        filterByDropdownIsExpanded: false,
        viewModeDropdownIsExpanded: false,
        viewOptionsIsExpanded: false
      };
    }

  toggleOrderByDropdown = (event) => {
    if (event) {
      event.preventDefault();
    }
    this.setState({
      orderByDropdownIsExpanded: !this.state.orderByDropdownIsExpanded,
      viewModeDropdownIsExpanded: false,
      viewOptionsIsExpanded: false,
      filterByDropdownIsExpanded: false
    });
  };

  toggleFilterByDropdown = (event) => {
    if (event) {
      event.preventDefault();
    }
    this.setState({
      filterByDropdownIsExpanded: !this.state.filterByDropdownIsExpanded,
      orderByDropdownIsExpanded: false,
      viewModeDropdownIsExpanded: false,
      viewOptionsIsExpanded: false
    });
  };

  toggleViewModeDropdown = (event) => {
    if (event) {
      event.preventDefault();
    }
    this.setState({
      viewModeDropdownIsExpanded: !this.state.viewModeDropdownIsExpanded,
      viewOptionsIsExpanded: false,
      orderByDropdownIsExpanded: false,
      filterByDropdownIsExpanded: false
    });
  };

  toggleOptionsMenu = (event) => {
    if (event) {
      event.preventDefault();
    }
    this.setState({
      viewOptionsIsExpanded: !this.state.viewOptionsIsExpanded,
      viewModeDropdownIsExpanded: false,
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
          />
        <li>
          <hr/>
        </li>
        <ViewSwitcherContainer
          menuExpanded={this.state.viewModeDropdownIsExpanded}
          optionsExpanded={this.state.viewOptionsIsExpanded}
          toggleDropdown={this.toggleViewModeDropdown}
          toggleOptionsMenu={this.toggleOptionsMenu}
          />
      </ListFrameMenu>
    );
  }
}