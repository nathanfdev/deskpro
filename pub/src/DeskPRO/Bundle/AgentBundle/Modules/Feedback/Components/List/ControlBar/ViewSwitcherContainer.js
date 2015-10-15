import React, {Component, PropTypes} from 'react';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned';
import ViewSwitcherDropdown from './ViewSwitcherDropdown';
import {FeedbackViewOptions} from './FeedbackViewOptions';
import { VIEW_MODE_TABLE, VIEW_MODE_CARD } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

import { connect } from 'react-redux';
@connect(state => ({
  order: state.Feedback.list.get('order'),
  filters: state.Feedback.list.get('filters'),
  tableViewFields: state.Feedback.list.get('tableViewFields'),
  listViewFields: state.Feedback.list.get('listViewFields'),
  currentViewMode: state.Application.routing.getIn(['hash', 'list', 'view'], 'card')
}))

export class ViewSwitcherContainer extends Component {

  static propTypes = {
    order: PropTypes.string.isRequired,
    menuExpanded: PropTypes.bool.isRequired,
    optionsExpanded: PropTypes.bool.isRequired,
    currentViewMode: PropTypes.string.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    toggleOptionsMenu: PropTypes.func.isRequired
  };

  render() {
    const { dispatch, menuExpanded, optionsExpanded, currentViewMode, toggleDropdown, toggleOptionsMenu } = this.props;
    const viewModesData = {
      [VIEW_MODE_TABLE]: {label: 'Table view', icon: 'table'},
      [VIEW_MODE_CARD]: {label: 'Card view', icon: 'list'}
    };
    return (
      <ViewModeSwitcher
        {...this.props}
        currentViewMode={viewModesData[currentViewMode]}
        ref="viewModeButton"
        >
        <Positioned isOpen={menuExpanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.viewModeButton}>
          <ViewSwitcherDropdown
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
            currentViewMode={currentViewMode}
            dispatch={dispatch}
          />
        </Positioned>
      </ViewModeSwitcher>
    );
  }
}