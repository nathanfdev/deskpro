import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ListItem, ListItemLabelSpinner } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { startFilterEditing } from '../../../../Actions/navActions';
import { applyListParams } from '../../../../Actions/listActions';
import { FilterEditPopupContainer } from '../../FilterEditPopupContainer';
import { loadingFilterIdsSelector } from '../../../../Selectors/nav';

@connect(state => ({
  notDoneFilters: loadingFilterIdsSelector(state)
}))
export class ListItemContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    notDoneFilters: PropTypes.object.isRequired,
    count: PropTypes.number.isRequired,
    id: PropTypes.number.isRequired,
    isTopLevel: PropTypes.bool.isRequired,
    listFilters: PropTypes.object.isRequired,
    type: PropTypes.string.isRequired,
    title: PropTypes.string.isRequired,
    parentIsLoading: PropTypes.bool,
    children: PropTypes.node
  };

  render() {
    const { dispatch, count, id, type, notDoneFilters, isTopLevel, listFilters, children } = this.props;

    const props = {
      count,
      onClick: () => dispatch(applyListParams(listFilters)),
      onItemControlClick: isTopLevel ? this.startFilterEditing(id) : null
    };

    let label = this.getItemLabel();
    const isNotDoneFilterItem = (type === 'filter') && notDoneFilters.includes(id);
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
          {isTopLevel ? <FilterEditPopupContainer attachTo={this.refs.item} filterId={id} /> : ''}
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
    const { type, id, parentIsLoading, title } = this.props;

    if (parentIsLoading) {
      return <ListItemLabelSpinner />;
    }

    const useIdAsLabel = ['waiting_time', 'all_waiting_time', 'open_time'].indexOf(type) > -1;
    const label = useIdAsLabel ? id : title;

    return label ? label : '—';
  }
}
