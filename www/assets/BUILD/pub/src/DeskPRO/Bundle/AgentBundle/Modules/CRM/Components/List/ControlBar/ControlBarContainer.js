import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import {
  currentListParamsSelector,
  listFiltersSelector,
  currentViewModeSelector,
  currentContentSelector,
  peopleFieldsSelector,
  orgFieldsSelector,
} from '../../../Selectors/list';
import {
  applyParams,
  togglePeopleFieldVisibility,
  toggleOrgFieldVisibility,
  changePeopleFieldOrder,
  changeOrgFieldOrder
} from '../../../Actions/crmListActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  content:       currentContentSelector(state),
  filters:       listFiltersSelector(state),
  currentParams: currentListParamsSelector(state),
  viewMode:      currentViewModeSelector(state),
  peopleFields:  peopleFieldsSelector(state),
  orgFields:     orgFieldsSelector(state)
}), {
  togglePeopleFieldVisibility,
  toggleOrgFieldVisibility,
  changePeopleFieldOrder,
  changeOrgFieldOrder
})
export class ControlBarContainer extends Component {

  static propTypes = {
    content:                     PropTypes.string.isRequired,
    filters:                     PropTypes.array.isRequired,
    currentParams:               PropTypes.object.isRequired,
    viewMode:                    PropTypes.string.isRequired,
    peopleCardFields:            PropTypes.object.isReqiured,
    peopleTableFields:           PropTypes.object.isReqiured,
    orgCardFields:               PropTypes.object.isReqiured,
    orgTableFields:              PropTypes.object.isReqiured,
    togglePeopleFieldVisibility: PropTypes.func.isReqiured,
    toggleOrgFieldVisibility:    PropTypes.func.isReqiured,
    changePeopleFieldOrder:      PropTypes.func.isReqiured,
    changeOrgFieldOrder:         PropTypes.func.isReqiured
  };

  getPeopleConfig() {
    const { filters, currentParams, peopleFields, togglePeopleFieldVisibility, changePeopleFieldOrder } = this.props;

    return {
      applyParams,
      currentParams,
      filters,

      sorting: {
        date_created:    {
          label: 'Created',
          icon:  'calendar'
        },
        name:            {
          label: 'Name',
          icon:  'sort-alpha-asc'
        },
        date_last_login: {
          label: 'Last login',
          icon:  'calendar'
        },
        organization:    {
          label: 'Organization',
          icon:  'building-o'
        }
      },

      view: {
        options: {
          [constants.VIEW_MODE_CARD]: {
            label:  'Card View',
            icon:   'list',
            fields: peopleFields.get(constants.VIEW_MODE_CARD)
          },

          [constants.VIEW_MODE_TABLE]: {
            label:  'Table View',
            icon:   'table',
            fields: peopleFields.get(constants.VIEW_MODE_TABLE)
          }
        },

        viewMode:              this.props.viewMode,
        viewModeAction:        (mode) => updateRoutingState('list', 'view', mode),
        toggleFieldVisibility: togglePeopleFieldVisibility,
        changeFieldOrder:      changePeopleFieldOrder
      }
    };
  }

  getOrganizationConfig() {
    const { filters, currentParams, orgFields, toggleOrgFieldVisibility, changeOrgFieldOrder } = this.props;

    return {
      applyParams,
      currentParams,
      filters,

      sorting: {
        date_created: {
          label: 'Created',
          icon:  'calendar'
        },
        name:         {
          label: 'Name',
          icon:  'sort-alpha-asc'
        }
      },

      view: {
        options: {
          [constants.VIEW_MODE_CARD]: {
            label:  'Card View',
            icon:   'list',
            fields: orgFields.get(constants.VIEW_MODE_CARD)
          },

          [constants.VIEW_MODE_TABLE]: {
            label:  'Table View',
            icon:   'table',
            fields: orgFields.get(constants.VIEW_MODE_TABLE)
          }
        },

        viewMode:              this.props.viewMode,
        viewModeAction:        (mode) => updateRoutingState('list', 'view', mode),
        toggleFieldVisibility: toggleOrgFieldVisibility,
        changeFieldOrder:      changeOrgFieldOrder
      }
    };
  }

  render() {
    const config = this.props.content === 'people' ? this.getPeopleConfig() : this.getOrganizationConfig();

    return <ControlBar {...config} />;
  }
}
