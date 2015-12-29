import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { currentListSortSelector, currentListOrderSelector, currentListParamsSelector, listFiltersSelector, currentViewModeSelector, currentContentSelector }
  from '../../../Selectors/list';
import { setSort, setOrder, applyParams }
  from '../../../Actions/crmListActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  sort: currentListSortSelector(state),
  order: currentListOrderSelector(state),
  viewMode: currentViewModeSelector(state),
  content: currentContentSelector(state)
}))
export class ControlBarContainer extends Component {
  static propTypes = {
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    viewMode: PropTypes.string.isRequired,
    content: PropTypes.string.isRequired
  };

  render() {
    const {content} = this.props;
    const config = {
      onMenuUnmount: applyParams,
      sorting: {
        options: {
          date_created: { label: 'Created', icon: 'calendar' },
          name: { label: 'Name', icon: 'sort-alpha-asc' }
        },
        sort: this.props.sort,
        order: this.props.order,
        sortAction: setSort,
        orderAction: setOrder
      },
      view: {
        options: {
          [constants.VIEW_MODE_CARD]: {
            label: 'Card View',
            icon: 'list',

            configurableFields: {
              id: 'ID',
              date_created: 'Date created',
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

    if (content === 'people') {
      config.sorting.options.date_last_login = { label: 'Last login', icon: 'calendar' };
      config.sorting.options.organization = { label: 'Organization', icon: 'building-o' };
    }
    return (
      <ControlBar {...config} />
    );
  }
}