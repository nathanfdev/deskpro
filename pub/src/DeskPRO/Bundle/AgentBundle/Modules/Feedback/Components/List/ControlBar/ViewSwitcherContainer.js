import React, {Component, PropTypes} from 'react';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { viewDataSelector } from '../../../Selectors/list';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned';
import { toggleViewMode } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import ViewSwitcherDropdown from './ViewSwitcherDropdown';

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
    expanded: PropTypes.bool.isRequired,
    currentViewMode: PropTypes.object.isRequired,
    viewModeOptions: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  };

  render() {
    const {dispatch, expanded, viewModeOptions, currentViewMode, toggleDropdown} = this.props;
    return (
      <ViewModeSwitcher
        {...this.props}
        ref="viewModeButton"
        >
        <Positioned isOpen={expanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.viewModeButton}>
          <ViewSwitcherDropdown
            viewModeOptions={viewModeOptions}
            currentViewMode={currentViewMode}
            toggleDropdown={toggleDropdown}
            dispatch={dispatch} />
        </Positioned>
      </ViewModeSwitcher>
    );
  }
}