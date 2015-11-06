import React from 'react';
import { connect } from 'react-redux';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ViewModeDropdown } from './ViewModeDropdown';

const viewModeOptions = [
  { field: 'list', label: 'Card View', icon: 'list' },
  { field: 'table', label: 'Table View', icon: 'table' },
  { field: 'kanban', label: 'Kanban', icon: 'sticky-note-o' },
  { field: 'calendar', label: 'Calendar', icon: 'calendar' }
];

@connect()
export class ViewModeSwitcherContainer extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      dropdownOpened: false
    };
  }

  onOpenDropdown = () => {
    this.setState({
      dropdownOpened: true
    });
  };

  onCloseDropDown = () => {
    this.setState({
      dropdownOpened: false
    });
  };

  render() {
    const currentViewModeOption = viewModeOptions[0];

    return (
      <ViewModeSwitcher ref="button"
               toggleDropdown={this.onOpenDropdown}>

        <Detached isOpen={this.state.dropdownOpened}
                  positionAt="left bottom"
                  positionTarget={this.refs.button}>

          <ClickOut onClickOut={this.onCloseDropDown}>
            <ViewModeDropdown currentViewModeOption={currentViewModeOption}
                              viewModeOptions={viewModeOptions} />
          </ClickOut>
        </Detached>
      </ViewModeSwitcher>
    );
  }
}
