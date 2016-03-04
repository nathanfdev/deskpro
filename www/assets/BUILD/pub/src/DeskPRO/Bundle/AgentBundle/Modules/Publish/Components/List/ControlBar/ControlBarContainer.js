import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { currentListOrderBySelector, currentListOrderDirSelector, currentListParamsSelector, listFiltersSelector, currentViewModeSelector }
  from '../../../Selectors/list';
import { setOrderBy, setOrderDir, applyParams }
  from '../../../Actions/publishListActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/ControlBar';

@connect(state => ({
  orderBy: currentListOrderBySelector(state),
  orderDir: currentListOrderDirSelector(state),
  filterParams: currentListParamsSelector(state),
  filters: listFiltersSelector(state),
  viewMode: currentViewModeSelector(state)
}))
export class ControlBarContainer extends Component {
  static propTypes = {
    orderBy: PropTypes.string.isRequired,
    orderDir: PropTypes.string.isRequired,
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