import React from 'react';
import { connect } from 'react-redux';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ViewModeDropdown } from './ViewModeDropdown';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const viewModeOptions = [
  { type: constants.VIEW_MODE_CARD, label: 'Card View', icon: 'list' },
  { type: constants.VIEW_MODE_TABLE, label: 'Table View', icon: 'table' },
  { type: constants.VIEW_MODE_KANBAN, label: 'Kanban', icon: 'sticky-note-o' },
  { type: constants.VIEW_MODE_CALENDAR, label: 'Calendar', icon: 'calendar' }
];

@connect(state => ({
  currentView: state.Tasks.tasks.get('view')
}))
export class ViewModeContainer extends React.Component {

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
    return (
      <ViewModeSwitcher ref="button"
               toggleDropdown={this.onOpenDropdown}>

        <Detached isOpen={this.state.dropdownOpened}
                  positionAt="left bottom"
                  positionTarget={this.refs.button}>

          <ClickOut onClickOut={this.onCloseDropDown}>
            <ViewModeDropdown viewModeOptions={viewModeOptions} {...this.props} />
          </ClickOut>
        </Detached>
      </ViewModeSwitcher>
    );
  }
}
