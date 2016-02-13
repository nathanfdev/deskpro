import React, { Component, PropTypes } from 'react';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { connect } from 'react-redux';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';
import { listSortSelector, listOrderSelector, viewModeSelector }
  from '../../../Selectors/list';
import { changeSort, toggleOrder, applyParams }
  from '../../../Actions/chatListActions';

@connect(state => ({
  sort: listSortSelector(state),
  order: listOrderSelector(state),
  viewMode: viewModeSelector(state)
}))
export class ControlBarContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    viewMode: PropTypes.string.isRequired
  };

  render() {
    const config = {
      onMenuUnmount: applyParams,
      sorting: {
        options: {
          date_created: { label: 'Date', icon: 'calendar' },
          agent: { label: 'Agent', icon: 'calendar' },
          department: { label: 'Department', icon: 'calendar-o' }
        },
        sort: this.props.sort,
        order: this.props.order,
        sortAction: changeSort,
        orderAction: toggleOrder
      },
      view: {
        options: {
          [constants.VIEW_MODE_CARD]: {
            label: 'Card View',
            icon: 'list',

            configurableFields: {
              id: 'ID',
              urgency: 'Urgency',
              person: 'Person',
              date_created: 'Date created',
              labels: 'Labels'
            }
          },
          [constants.VIEW_MODE_TABLE]: {
            label: 'Table View',
            icon: 'table',

            configurableFields: {
              id: 'ID',
              urgency: 'Urgency',
              person: 'Person',
              person_email: 'Person email',
              agent: 'Agent',
              subject: 'Subject',
              status: 'Status',
              date_created: 'Date created',
              labels: 'Labels'
            }
          }
        },

        viewMode: this.props.viewMode,
        viewModeAction: (mode) => updateRoutingState('list', 'view', mode)
      }
    };

    return (
      <ControlBar {...config} />
    );
  }
}
