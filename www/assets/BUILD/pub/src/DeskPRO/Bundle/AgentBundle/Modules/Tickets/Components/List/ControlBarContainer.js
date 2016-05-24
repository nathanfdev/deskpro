import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ControlBar } from '../../../Common/Components/ListFrame/ControlBar/ControlBar';
import { constants } from '../../../../Constants/Constants';
import {
  tableVisibleFieldsSelector,
  cardVisibleFieldsSelector,
  currentViewModeSelector,
  listParamsSelector,
  listOrderBySelector,
  listOrderDirSelector
}
  from '../../Selectors/list';
import { updateRoutingState } from '../../../Application/Actions/routingActions';
import {
  toggleTableFieldVisibility, toggleCardFieldVisibility, applyListParams
}
  from '../../Actions/listActions';
import { labelsSelector } from '../../Selectors/nav';
import { listFiltersSelector } from '../../Selectors/filters';

@connect(state => ({
  viewMode:           currentViewModeSelector(state),
  orderBy:            listOrderBySelector(state),
  orderDir:           listOrderDirSelector(state),
  tableVisibleFields: tableVisibleFieldsSelector(state),
  cardVisibleFields:  cardVisibleFieldsSelector(state),
  currentParams:      listParamsSelector(state),
  filters:            listFiltersSelector(state),
  labels:             labelsSelector(state)
}))

export class ControlBarContainer extends Component {
  static propTypes = {
    dispatch:           PropTypes.func.isRequired,
    orderBy:            PropTypes.string.isRequired,
    orderDir:           PropTypes.string.isRequired,
    tableVisibleFields: PropTypes.object.isRequired,
    cardVisibleFields:  PropTypes.object.isRequired,
    viewMode:           PropTypes.string.isRequired,
    filters:            PropTypes.array.isRequired,
    currentParams:      PropTypes.object.isRequired,
    labels:             PropTypes.object.isRequired
  };

  render() {
    const { currentParams, filters } = this.props;
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
            label:                 'Card View',
            icon:                  'list',
            visibleFields:         this.props.cardVisibleFields,
            toggleFieldVisibility: toggleCardFieldVisibility,

            configurableFields: {
              id:           'ID',
              urgency:      'Urgency',
              person:       'Person',
              date_created: 'Date created',
              labels:       'Labels'
            }
          },
          
          [constants.VIEW_MODE_TABLE]: {
            label:                 'Table View',
            icon:                  'table',
            visibleFields:         this.props.tableVisibleFields,
            toggleFieldVisibility: toggleTableFieldVisibility,

            configurableFields: {
              id:           'ID',
              urgency:      'Urgency',
              person:       'Person',
              person_email: 'Person email',
              agent:        'Agent',
              subject:      'Subject',
              status:       'Status',
              date_created: 'Date created',
              labels:       'Labels'
            }
          }
        },

        viewMode:       this.props.viewMode,
        viewModeAction: (mode) => updateRoutingState('list', 'view', mode)
      }
    };

    return (
      <ControlBar {...config} />
    );
  }
}
