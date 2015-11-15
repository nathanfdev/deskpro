import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ListItem, ListItemLabelSpinner } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { startFilterEditing } from '../../../../Actions/navActions';
import { navItemLabelsSelector } from '../../../../Selectors/nav-item-labels';
import { applyListParams } from '../../../../Actions/listActions';
import { FilterEditPopupContainer } from '../../FilterEditPopupContainer';
import { loadingFilterIdsSelector } from '../../../../Selectors/nav';

@connect(state => ({
  notDoneFilters: loadingFilterIdsSelector(state),
  labels: navItemLabelsSelector(state)
}))
export class ListItemContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    labels: PropTypes.object.isRequired,
    notDoneFilters: PropTypes.object.isRequired,
    count: PropTypes.number.isRequired,
    group: PropTypes.number.isRequired,
    isTopLevel: PropTypes.bool.isRequired,
    listFilters: PropTypes.object.isRequired,
    grouped_by: PropTypes.string.isRequired,
    parentIsLoading: PropTypes.bool,
    children: PropTypes.node
  };

  render() {
    const { dispatch, count, group, grouped_by, notDoneFilters, isTopLevel, listFilters, children } = this.props;

    const props = {
      count,
      onClick: () => dispatch(applyListParams(listFilters)),
      onItemControlClick: isTopLevel ? this.startFilterEditing(group) : null
    };

    let label = this.getItemLabel();
    const isNotDoneFilterItem = (grouped_by === 'filter') && notDoneFilters.includes(group);
    if (isNotDoneFilterItem) {
      label = (
        <div style={{paddingLeft: '17px'}}>
          <ListItemLabelSpinner />
          {label}
        </div>
      );
    }

    return (
      <ListItem {...props} ref="item">
        <div part="label">{label}</div>
        <div part="nested">
          {children}
          {isTopLevel ? <FilterEditPopupContainer attachTo={this.refs.item} filterId={group} /> : ''}
        </div>
      </ListItem>
    );
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
    const { labels, grouped_by, group, parentIsLoading } = this.props;

    if (parentIsLoading) {
      return <ListItemLabelSpinner />;
    }

    const useGroupAsLabel = ['waiting_time', 'all_waiting_time', 'open_time'].indexOf(grouped_by) > -1;
    const defaultLabel = group ? <ListItemLabelSpinner /> : '—';
    const label = useGroupAsLabel ? group : labels.getIn([grouped_by, group], defaultLabel);

    return label;
  }
}
