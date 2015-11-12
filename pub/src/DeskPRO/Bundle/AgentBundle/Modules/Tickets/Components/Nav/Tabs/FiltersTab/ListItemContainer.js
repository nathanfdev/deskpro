import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ListItem, ListItemLabelSpinner, ListItemSpinner }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { startFilterEditing } from '../../../../Actions/navActions';
import { navItemLabelsSelector } from '../../../../Selectors/nav-item-labels';
import { loadingFilterIdsSelector } from '../../../../Selectors/nav';
import { applyListParams } from '../../../../Actions/listActions';
import { FilterEditPopupContainer } from '../../FilterEditPopupContainer';

@connect(state => ({
  notDoneFilters: loadingFilterIdsSelector(state),
  labels: navItemLabelsSelector(state)
}))
export class ListItemContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    labels: PropTypes.object.isRequired,
    count: PropTypes.number.isRequired,
    group: PropTypes.number.isRequired,
    isTopLevel: PropTypes.bool.isRequired,
    listFilters: PropTypes.object.isRequired,
    grouped_by: PropTypes.string,
    children: PropTypes.node
  };

  render() {
    const { dispatch, count, group, grouped_by, isTopLevel, notDoneFilters, listFilters, children } = this.props;

    const props = {
      count,
      children,
      onClick: () => dispatch(applyListParams(listFilters)),
      label: this.getItemLabel(),
      onItemControlClick: isTopLevel ? this.startFilterEditing(group) : null
    };

    // check if filter is being loaded to show spinner
    const isNotDoneFilterItem = (grouped_by === 'filter') && notDoneFilters.includes(group);

    return isNotDoneFilterItem
      ? <ListItemSpinner />
      : <div>
          {isTopLevel ? <FilterEditPopupContainer attachTo={this.refs.item} filterId={group} /> : ''}
          <ListItem {...props} ref="item" />
        </div>
      ;
  }

  startFilterEditing(filterId) {
    return () => this.props.dispatch(startFilterEditing(filterId));
  }

  /**
   * Get item label
   *
   * @return {string} Label
   */
  getItemLabel() {
    const { labels, grouped_by, group } = this.props;

    const useGroupAsLabel = ['waiting_time', 'all_waiting_time', 'open_time'].indexOf(grouped_by) > -1;
    const defaultLabel = group ? <ListItemLabelSpinner /> : '—';
    const label = useGroupAsLabel ? group : labels.getIn([grouped_by, group], defaultLabel);

    return label;
  }
}
