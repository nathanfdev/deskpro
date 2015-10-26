import React, {Component, PropTypes} from 'react';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ViewSwitcherDropdownStatefulContainer } from './ViewSwitcherDropdownStatefulContainer';
import { FeedbackViewOptions } from './FeedbackViewOptions';
import { VIEW_MODE_TABLE, VIEW_MODE_CARD } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { currentViewModeSelector } from '../../../Selectors/list';
import { getDisplayFieldsFromPersonSetting } from '../../../Actions/FeedbackListActions';


import { connect } from 'react-redux';
@connect(state => ({
  order: state.Feedback.list.get('order'),
  filters: state.Feedback.list.get('filters'),
  viewFields: state.Feedback.list.get('viewFields'),
  currentViewMode: currentViewModeSelector(state)
}))
export class ViewSwitcherContainer extends Component {

  static propTypes = {
    order: PropTypes.string.isRequired,
    menuExpanded: PropTypes.bool.isRequired,
    optionsExpanded: PropTypes.bool.isRequired,
    currentViewMode: PropTypes.string.isRequired,
    viewFields: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    toggleOptionsMenu: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    const { dispatch } = this.props;
    dispatch(getDisplayFieldsFromPersonSetting());
  }

  render() {
    const { dispatch, menuExpanded, optionsExpanded, currentViewMode, toggleDropdown, toggleOptionsMenu, viewFields } = this.props;
    const viewModesData = {
      [VIEW_MODE_TABLE]: { label: 'Table view', icon: 'table' },
      [VIEW_MODE_CARD]: { label: 'Card view', icon: 'list' }
    };
    return (
      <ViewModeSwitcher
        toggleDropdown={toggleDropdown}
        currentViewMode={viewModesData[currentViewMode]}
        ref="viewModeButton"
        >
        <Positioned isOpen={menuExpanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.viewModeButton}>
          <ViewSwitcherDropdownStatefulContainer
            currentViewMode={currentViewMode}
            toggleDropdown={toggleDropdown}
            toggleOptionsMenu={toggleOptionsMenu}
            dispatch={dispatch}
            />
        </Positioned>
        <Positioned isOpen={optionsExpanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.viewModeButton}>
          <FeedbackViewOptions
            viewFields={viewFields}
            currentViewMode={currentViewMode}
            dispatch={dispatch}
            toggleOptionsMenu={toggleOptionsMenu}
            />
        </Positioned>
      </ViewModeSwitcher>
    );
  }
}