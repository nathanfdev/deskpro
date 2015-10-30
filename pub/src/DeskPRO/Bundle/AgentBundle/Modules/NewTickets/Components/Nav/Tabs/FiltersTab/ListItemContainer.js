import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ListItem, ListItemLabelSpinner, ListItemSpinner }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { startFilterEditing } from '../../../../Actions/navActions';
import { navItemLabelsSelector } from '../../../../Selectors/nav-item-labels';
import { loadingFilterIdsSelector } from '../../../../Selectors/nav';

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
    editable: PropTypes.bool.isRequired,
    grouped_by: PropTypes.string,
    children: PropTypes.node
  };

  render() {
    const { count, group, grouped_by, editable, notDoneFilters, children } = this.props;

    const props = {
      count,
      children,
      label: this.getItemLabel(),
      onClick: () => alert('list item clicked'),
      onItemControlClick: editable ? this.startFilterEditing(group) : null
    };
    const isNotDoneFilterItem = (grouped_by === 'filter') && notDoneFilters.includes(group);

    return isNotDoneFilterItem ? <ListItemSpinner /> : <ListItem {...props} />;
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
