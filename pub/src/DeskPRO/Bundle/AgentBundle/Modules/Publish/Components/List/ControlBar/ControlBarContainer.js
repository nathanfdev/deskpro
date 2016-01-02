import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { currentListSortSelector, currentListOrderSelector, currentListParamsSelector, listFiltersSelector, currentViewModeSelector }
  from '../../../Selectors/list';
import { setSort, setOrder, applyParams }
  from '../../../Actions/publishListActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  sort: currentListSortSelector(state),
  order: currentListOrderSelector(state),
  filterParams: currentListParamsSelector(state),
  filters: listFiltersSelector(state),
  viewMode: currentViewModeSelector(state)
}))
export class ControlBarContainer extends Component {
  static propTypes = {
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    filterParams: PropTypes.object.isRequired,
    filters: PropTypes.array.isRequired,
    viewMode: PropTypes.string.isRequired
  };

  render() {
    const config = {
      onMenuUnmount: applyParams,
      sorting: {
        options: {
          date_created: { label: 'Created', icon: 'calendar' },
          date_updated: { label: 'Updated', icon: 'calendar-o' },
          person: { label: 'Author', icon: 'calendar' }
        },
        sort: this.props.sort,
        order: this.props.order,
        sortAction: setSort,
        orderAction: setOrder
      },
      filtering: {
        filters: this.props.filters,
        setParamsAction: applyParams,
        state: this.props.filterParams
      },
      view: {
        options: {
          [constants.VIEW_MODE_CARD]: {
            label: 'Card View',
            icon: 'list',

            configurableFields: {
              id: 'ID',
              date_created: 'Date created'
            }
          },
          [constants.VIEW_MODE_TABLE]: {
            label: 'Table View',
            icon: 'table',

            configurableFields: {
              id: 'ID',
              title: 'Title',
              person: 'Person',
              content: 'Content',
              date_created: 'Date created'
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