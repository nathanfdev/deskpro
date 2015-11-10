import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import {
  selectedCountSelector, listSortSelector, listOrderSelector, tableVisibleFieldsSelector, cardVisibleFieldsSelector,
  viewModeSelector
} from '../../Selectors/list';
import {
  toggleAll, setViewMode, setSort, setOrder, toggleTableFieldVisibility, toggleCardFieldVisibility
} from '../../Actions/listActions';

@connect(state => ({
  selectedCount: selectedCountSelector(state),
  sort: listSortSelector(state),
  order: listOrderSelector(state),
  viewMode: viewModeSelector(state),
  tableVisibleFields: tableVisibleFieldsSelector(state),
  cardVisibleFields: cardVisibleFieldsSelector(state)
}))
export class ControlBarContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    selectedCount: PropTypes.number.isRequired,
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    tableVisibleFields: PropTypes.array.isRequired,
    cardVisibleFields: PropTypes.array.isRequired,
    viewMode: PropTypes.string.isRequired
  };

  render() {
    const config = {
      checkbox: {
        count: this.props.selectedCount,
        action: toggleAll
      },
      sorting: {
        options: [
          {field: 'date_created', label: 'Date', icon: 'calendar'},
          {field: 'urgency', label: 'Urgency', icon: 'calendar-o'}
        ],
        sort: this.props.sort,
        order: this.props.order,
        sortAction: setSort,
        orderAction: setOrder
      },
      view: {
        options: [
          {field: constants.VIEW_MODE_CARD, label: 'Card View', icon: 'list'},
          {field: constants.VIEW_MODE_TABLE, label: 'Table View', icon: 'table'}
        ],
        viewMode: this.props.viewMode,
        viewModeAction: setViewMode,

        tableConfigurableFields: {
          id: 'ID',
          urgency: 'Urgency',
          person: 'Person',
          person_email: 'Person email',
          agent: 'Agent',
          subject: 'Subject',
          status: 'Status',
          date_created: 'Date created',
          labels: 'Labels'
        },
        tableVisibleFields: this.props.tableVisibleFields,
        tableToggleFieldVisibility: toggleTableFieldVisibility,

        cardConfigurableFields: {
          id: 'ID',
          urgency: 'Urgency',
          person: 'Person',
          date_created: 'Date created',
          labels: 'Labels'
        },
        cardVisibleFields: this.props.cardVisibleFields,
        cardToggleFieldVisibility: toggleCardFieldVisibility
      }
    };

    return (
      <ControlBar {...config} />
    );
  }
}