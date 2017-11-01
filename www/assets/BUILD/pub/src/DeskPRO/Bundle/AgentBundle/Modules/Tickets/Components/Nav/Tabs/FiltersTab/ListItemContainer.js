import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { ListItem, ListItemLabelSpinner, ListItemStatefulContainer }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { routingStateSelector } from '../../../../../Application/Selectors/routing';
import { startFilterEditing } from '../../../../Actions/navActions';
import { applyListParams } from '../../../../Actions/listActions';
import { ListGroupingModalContainer } from '../../ListGroupingModalContainer';
import { loadingFilterIdsSelector } from '../../../../Selectors/nav';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';

@connect(state => ({
  hash:           routingStateSelector(state),
  notDoneFilters: loadingFilterIdsSelector(state)
}))
export class ListItemContainer extends Component {
  static propTypes = {
    dispatch:       PropTypes.func.isRequired,
    hash:           PropTypes.object,
    notDoneFilters: PropTypes.object.isRequired,
    count:          PropTypes.number.isRequired,
    id:             PropTypes.number.isRequired,
    parentTitle:    PropTypes.string,
    isTopLevel:     PropTypes.bool.isRequired,
    listFilters:    PropTypes.object.isRequired,
    type:           PropTypes.string.isRequired,
    title:          PropTypes.string.isRequired,
    children:       PropTypes.node
  };

  constructor(props) {
    super(props);
    this.itemId = urlSanitize(props.parentTitle ? `${props.parentTitle}~${props.title}` : props.title);
  }

  componentDidMount() {
    const { hash, listFilters, dispatch } = this.props;
    const activeItemId = hash.get('nav') ? hash.get('nav').get('active') : null;
    if (activeItemId === this.itemId) {
      dispatch(applyListParams(listFilters));
    }
  }

  /**
   * Get item label
   *
   * @return {string} Label
   */
  getItemLabel() {
    const { type, id, title } = this.props;
    const useIdAsLabel = ['waiting_time', 'all_waiting_time', 'open_time'].indexOf(type) > -1;
    const label        = useIdAsLabel ? id : title;

    return label || '—';
  }

  startFilterEditing(filterId) {
    return () => this.props.dispatch(startFilterEditing(filterId));
  }

  render() {
    const { dispatch, count, id, type, notDoneFilters, isTopLevel, listFilters, children, title } = this.props;
    const props = {
      count,
      children,

      onClick:            () => dispatch(applyListParams(listFilters)),
      onItemControlClick: isTopLevel ? this.startFilterEditing(id) : null,
      groupId:            'nav',
      itemId:             this.itemId,
      label:              title
    };

    let label                 = this.getItemLabel();
    const isNotDoneFilterItem = (type === 'filter') && notDoneFilters.includes(id);
    if (isNotDoneFilterItem) {
      label = (
        <div style={{ paddingLeft: '17px' }}>
          <ListItemLabelSpinner />
          {label}
        </div>
      );
    }

    return (
      <ListItemStatefulContainer {...props}>
        <ListItem {...props} ref="item">
          <div part="label">{label}</div>
          <div part="nested">
            {children}
            {isTopLevel && <ListGroupingModalContainer attachTo={this.refs.item} filterId={id} />}
          </div>
        </ListItem>
      </ListItemStatefulContainer>
    );
  }
}
