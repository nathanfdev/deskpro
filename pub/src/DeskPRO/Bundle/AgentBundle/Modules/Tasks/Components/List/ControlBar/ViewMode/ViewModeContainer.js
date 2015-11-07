import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ViewModeDropdown } from './ViewModeDropdown';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { changeView } from '../../../../Actions/tasksActions';

const viewModeOptions = {
  [constants.VIEW_MODE_CARD]: { label: 'Card View', icon: 'list' },
  [constants.VIEW_MODE_TABLE]: { label: 'Table View', icon: 'table' },
  [constants.VIEW_MODE_KANBAN]: { label: 'Kanban View', icon: 'sticky-note-o' },
  [constants.VIEW_MODE_CALENDAR]: { label: 'Calendar View', icon: 'calendar' }
};

@connect(state => ({
  currentView: state.Tasks.tasks.get('view')
}))
export class ViewModeContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    currentView: PropTypes.string.isRequired
  };

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

  onChangeView = type => {
    this.props.dispatch(changeView(type));
    this.onCloseDropDown();
  };

  render() {
    const { currentView } = this.props;

    return (
      <ViewModeSwitcher ref="button"
                        toggleDropdown={this.onOpenDropdown}
                        currentViewMode={viewModeOptions[currentView]}>

        <Detached isOpen={this.state.dropdownOpened}
                  positionAt="left bottom"
                  positionTarget={this.refs.button}>

          <ClickOut onClickOut={this.onCloseDropDown}>
            <ViewModeDropdown viewModeOptions={viewModeOptions}
                              currentView={currentView}
                              onChangeView={this.onChangeView} />
          </ClickOut>
        </Detached>
      </ViewModeSwitcher>
    );
  }
}
