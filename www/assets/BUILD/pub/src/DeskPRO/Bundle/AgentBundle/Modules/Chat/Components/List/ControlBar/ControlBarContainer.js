import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { constants } from '../../../../../Constants/Constants';
import { connect } from 'react-redux';
import { updateRoutingState } from '../../../../Application/Actions/routingActions';
import { ControlBar } from '../../../../Common/Components/ListFrame/ControlBar/ControlBar';
import { currentListParamsSelector, viewModeSelector, cardFieldsSelector, tableFieldsSelector }
  from '../../../Selectors/list';
import { listFiltersSelector } from '../../../Selectors/filters';
import { applyParams, toggleFieldVisibility, changeFieldOrder } from '../../../Actions/listActions';

@connect(state => ({
  currentParams: currentListParamsSelector(state),
  filters:       listFiltersSelector(state),
  viewMode:      viewModeSelector(state),
  cardFields:    cardFieldsSelector(state),
  tableFields:   tableFieldsSelector(state)
}), {
  applyParams,
  toggleFieldVisibility,
  changeFieldOrder
})
export class ControlBarContainer extends Component {
  static propTypes = {
    filters:               PropTypes.array.isRequired,
    currentParams:         PropTypes.object.isRequired,
    viewMode:              PropTypes.string.isRequired,
    cardFields:            PropTypes.object.isRequired,
    tableFields:           PropTypes.object.isRequired,
    applyParams:           PropTypes.func.isRequired,
    toggleFieldVisibility: PropTypes.func.isRequired,
    changeFieldOrder:      PropTypes.func.isRequired
  };

  render() {
    const { filters, currentParams, applyParams, toggleFieldVisibility, changeFieldOrder, cardFields, tableFields } = this.props;
    const config = {
      filters,
      applyParams,
      currentParams,

      sorting: {
        date_created: {
          label: 'Date',
          icon:  'calendar'
        },
        agent: {
          label: 'Agent',
          icon:  'calendar'
        },
        department: {
          label: 'Department',
          icon:  'calendar-o'
        }
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

        viewMode:       this.props.viewMode,
        viewModeAction: (mode) => updateRoutingState('list', 'view', mode),
        toggleFieldVisibility,
        changeFieldOrder
      }
    };

    return (
      <ControlBar {...config} />
    );
  }
}
