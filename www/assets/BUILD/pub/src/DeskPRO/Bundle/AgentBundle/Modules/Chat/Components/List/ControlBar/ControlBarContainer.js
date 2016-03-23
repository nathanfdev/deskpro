import React, { Component, PropTypes } from 'react';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { connect } from 'react-redux';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';
import { currentListParamsSelector, viewModeSelector } from '../../../Selectors/list';
import { applyParams } from '../../../Actions/chatListActions';

@connect(state => ({
  currentParams: currentListParamsSelector(state),
  viewMode: viewModeSelector(state)
}))
export class ControlBarContainer extends Component {
  static propTypes = {
    currentParams: PropTypes.object.isRequired,
    viewMode: PropTypes.string.isRequired
  };

  render() {
    const config = {
      applyParams: applyParams,
      currentParams: this.props.currentParams,
      sorting: {
        date_created: { label: 'Date', icon: 'calendar' },
        agent: { label: 'Agent', icon: 'calendar' },
        department: { label: 'Department', icon: 'calendar-o' }
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
