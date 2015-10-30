import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { startFilterEditing } from '../../../../Actions/navActions';
import { navItemLabelsSelector } from '../../../../Selectors/nav-item-labels';

@connect(state => ({
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
    const { count, group, editable, children } = this.props;

    const props = {
      count,
      children,
      label: this.getItemLabel(),
      onClick: () => alert('list item clicked'),
      onItemControlClick: editable ? this.startFilterEditing(group) : null
    };

    return (
      <ListItem {...props} />
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
    const { labels, grouped_by, group } = this.props;

    const useGroupAsLabel = ['waiting_time', 'all_waiting_time', 'open_time'].indexOf(grouped_by) > -1;
    const label = useGroupAsLabel ? group : labels.getIn([grouped_by, group], group ? '...' : '—');

    return label;
  }
}
