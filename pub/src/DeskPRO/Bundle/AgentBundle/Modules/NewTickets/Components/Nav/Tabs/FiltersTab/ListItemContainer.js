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
   * Return urgency indicator if grouped by urgency, otherwise try to find label in this.props.labels and simply
   * return group name if label can't be found
   *
   * @return {string} Label
   */
  getItemLabel() {
    const { labels, grouped_by, group } = this.props;

    let label;
    if (grouped_by === 'urgency') {
      label = (
        <div className={'slider level-' + group}>
          <div className="slider-container">
          <span className="slider-grabber-wrapper">
            <span className="slider-grabber">{group}</span>
          </span>
          </div>
        </div>
      );
    } else {
      label = labels.getIn([grouped_by, group], group || '—');
    }

    return label;
  }
}
