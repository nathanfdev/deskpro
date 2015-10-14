import React, {Component, PropTypes} from 'react';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { viewDataSelector } from '../../../Selectors/list';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned';
import ViewSwitcherDropdown from './ViewSwitcherDropdown';
import {FeedbackViewOptions} from './FeedbackViewOptions';

import { connect } from 'react-redux';
@connect(state => ({
  order: state.Feedback.list.get('order'),
  filters: state.Feedback.list.get('filters'),
  viewModeOptions: state.Feedback.list.get('viewModeOptions'),
  tableViewFields: state.Feedback.list.get('tableViewFields'),
  listViewFields: state.Feedback.list.get('listViewFields'),
  currentViewMode: viewDataSelector(state)
}))

export class ViewSwitcherContainer extends Component {

  static propTypes = {
    order: PropTypes.string.isRequired,
    menuExpanded: PropTypes.bool.isRequired,
    optionsExpanded: PropTypes.bool.isRequired,
    currentViewMode: PropTypes.object.isRequired,
    viewModeOptions: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    toggleOptionsMenu: PropTypes.func.isRequired
  };

  render() {
    const {dispatch, menuExpanded, optionsExpanded, viewModeOptions, currentViewMode, toggleDropdown, toggleOptionsMenu} = this.props;
    return (
      <ViewModeSwitcher
        {...this.props}
        ref="viewModeButton"
        >
        <Positioned isOpen={menuExpanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.viewModeButton}>
          <ViewSwitcherDropdown
            viewModeOptions={viewModeOptions}
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
            viewModeOptions={viewModeOptions}
            currentViewMode={currentViewMode}
            dispatch={dispatch}
            />
        </Positioned>
      </ViewModeSwitcher>
    );
  }
}