import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { currentListOrderBySelector, currentListOrderDirSelector, currentListParamsSelector, listFiltersSelector, currentViewModeSelector, currentContentSelector }
  from '../../../Selectors/list';
import { setOrderBy, setOrderDir, applyParams }
  from '../../../Actions/crmListActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  content: currentContentSelector(state),
  orderBy: currentListOrderBySelector(state),
  orderDir: currentListOrderDirSelector(state),
  filters: listFiltersSelector(state),
  filterParams: currentListParamsSelector(state),
  viewMode: currentViewModeSelector(state)
}))
export class ControlBarContainer extends Component {

  static propTypes = {
    content: PropTypes.string.isRequired,
    orderBy: PropTypes.string.isRequired,
    orderDir: PropTypes.string.isRequired,
    filters: PropTypes.array.isRequired,
    filterParams: PropTypes.object.isRequired,
    viewMode: PropTypes.string.isRequired
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
        orderBy: this.props.orderBy,
        orderDir: this.props.orderDir,
        orderByAction: setOrderBy,
        orderDirAction: setOrderDir
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