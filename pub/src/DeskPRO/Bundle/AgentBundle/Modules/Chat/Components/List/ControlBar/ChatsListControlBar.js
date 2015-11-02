import React from 'react';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';
import { OrderByContainer } from './OrderByContainer';
import { ViewSwitcherContainer } from './ViewSwitcherContainer';

export class ChatsListControlBar extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      orderByDropdownIsExpanded: false,
      viewModeDropdownIsExpanded: false,
      dropdownOffset: { left: 0, top: 0 }
    };
  }

  toggleOrderByDropdown = (offset) => {
    this.setState({
      orderByDropdownIsExpanded: !this.state.orderByDropdownIsExpanded,
      viewModeDropdownIsExpanded: false,
      filterByDropdownIsExpanded: false,
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

  render() {
    return (
      <ListFrameMenu>
        <OrderByContainer toggleDropdown={this.toggleOrderByDropdown}/>
        <li>
          <hr/>
        </li>
        <ViewSwitcherContainer toggleDropdown={this.toggleViewModeDropdown}/>
      </ListFrameMenu>

    );
  }
}
