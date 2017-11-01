import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { ControlBar } from '../../../Common/Components/ListFrame/ControlBar/ControlBar';
import { constants } from '../../../../Constants/Constants';
import {
  tableFieldsSelector,
  cardFieldsSelector,
  currentViewModeSelector,
  listParamsSelector,
  listOrderBySelector,
  listOrderDirSelector
}
  from '../../Selectors/list';
import { updateRoutingState } from '../../../Application/Actions/routingActions';
import { toggleFieldVisibility, changeFieldOrder, applyListParams }
  from '../../Actions/listActions';
import { labelsSelector } from '../../Selectors/nav';
import { listFiltersSelector } from '../../Selectors/filters';

@connect(state => ({
  viewMode:      currentViewModeSelector(state),
  orderBy:       listOrderBySelector(state),
  orderDir:      listOrderDirSelector(state),
  tableFields:   tableFieldsSelector(state),
  cardFields:    cardFieldsSelector(state),
  currentParams: listParamsSelector(state),
  filters:       listFiltersSelector(state),
  labels:        labelsSelector(state)
}), {
  toggleFieldVisibility,
  changeFieldOrder
})
export class ControlBarContainer extends Component {
  static propTypes = {
    orderBy:               PropTypes.string.isRequired,
    orderDir:              PropTypes.string.isRequired,
    tableFields:           PropTypes.object.isRequired,
    cardFields:            PropTypes.object.isRequired,
    viewMode:              PropTypes.string.isRequired,
    filters:               PropTypes.array.isRequired,
    currentParams:         PropTypes.object.isRequired,
    labels:                PropTypes.object.isRequired,
    toggleFieldVisibility: PropTypes.func.isRequired,
    changeFieldOrder:      PropTypes.func.isRequired
  };

  render() {
    const { currentParams, tableFields, cardFields, filters } = this.props;
    const config = {
      currentParams,
      filters,

      applyParams: applyListParams,
      sorting:     {
        id:                   { label: 'ID', icon: 'calendar' },
        date_last_user_reply: { label: 'Last user reply date', icon: 'calendar' },
        urgency:              { label: 'Urgency', icon: 'calendar-o' }
      },

      view: {
        options: {

          [constants.VIEW_MODE_CARD]: {
            label:  'Card View',
            icon:   'list',
            fields: cardFields
          },

          [constants.VIEW_MODE_TABLE]: {
            label:  'Table View',
            icon:   'table',
            fields: tableFields
          }
        },

        viewMode:              this.props.viewMode,
        viewModeAction:        (mode) => updateRoutingState('list', 'view', mode),
        toggleFieldVisibility: this.props.toggleFieldVisibility,
        changeFieldOrder:      this.props.changeFieldOrder
      }
    };

    return (
      <ControlBar {...config} />
    );
  }
}
