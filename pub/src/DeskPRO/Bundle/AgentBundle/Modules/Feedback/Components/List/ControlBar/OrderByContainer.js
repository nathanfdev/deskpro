import React, {Component, PropTypes} from 'react';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import OrderByDropdown from './OrderByDropdown';
import { sortingDataSelector } from '../../../Selectors/list';
import { groupDataSelector } from '../../../Selectors/nav';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned';

import { connect } from 'react-redux';
@connect(state => ({
  sortOptions: state.Feedback.list.get('sortOptions').toJS(),
  order: state.Feedback.list.get('order'),
  currentSortMode: sortingDataSelector(state),
  currentGroup: groupDataSelector(state)
}))

export class OrderByContainer extends Component {

  static propTypes = {
    order: PropTypes.string.isRequired,
    expanded: PropTypes.bool.isRequired,
    currentSortMode: PropTypes.object.isRequired,
    sortOptions: PropTypes.object.isRequired,
    currentGroup: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  render() {
    const { dispatch, expanded, sortOptions, order, currentSortMode, currentGroup, toggleDropdown } = this.props;
    return (
      <OrderBy
        sortOptions={sortOptions}
        currentSortMode={currentSortMode}
        order={order}
        toggleDropdown={toggleDropdown}
        ref="orderButton"
        >
        <Positioned isOpen={expanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.orderButton}>
          <OrderByDropdown
            order={order}
            currentGroup={currentGroup}
            toggleDropdown={toggleDropdown}
            sortOptions={sortOptions}
            currentSortMode={currentSortMode}
            dispatch={dispatch}
            />
        </Positioned>
      </OrderBy>
    );
  }

}