import React, {Component, PropTypes} from 'react';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import OrderByDropdown from './OrderByDropdown';
import { sortingDataSelector, isCommentsSelector } from '../../../Selectors/list';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';

import { connect } from 'react-redux';
@connect(state => ({
  sortOptions: state.Feedback.list.get('sortOptions').toJS(),
  order: state.Feedback.list.get('order'),
  currentSortMode: sortingDataSelector(state),
  isComments: isCommentsSelector(state)
}))

export class OrderByContainer extends Component {

  static propTypes = {
    order: PropTypes.string.isRequired,
    expanded: PropTypes.bool.isRequired,
    currentSortMode: PropTypes.object.isRequired,
    sortOptions: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    dispatch: PropTypes.func.isRequired,
    isComments: PropTypes.bool.isRequired
  };

  render() {
    const { dispatch, expanded, sortOptions, order, currentSortMode, toggleDropdown, isComments } = this.props;

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
            toggleDropdown={toggleDropdown}
            sortOptions={sortOptions}
            currentSortMode={currentSortMode}
            dispatch={dispatch}
            isComments={isComments}
          />
        </Positioned>
      </OrderBy>
    );
  }

}