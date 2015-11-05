import React, {Component, PropTypes} from 'react';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import OrderByDropdown from './OrderByDropdown';
import { currentListSortSelector, currentListOrderSelector, isCommentsSelector } from '../../../Selectors/list';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { connect } from 'react-redux';

const sortOptions = [
  { field: 'date_created', label: 'Date', icon: 'calendar' },
  { field: 'total_rating', label: 'Rating', icon: 'calendar-o' },
  { field: 'num_ratings', label: 'Votes', icon: 'calendar' }
];

@connect(state => ({
  sort: currentListSortSelector(state),
  order: currentListOrderSelector(state),
  isComments: isCommentsSelector(state)
}))
export class OrderByContainer extends Component {

  static propTypes = {
    order: PropTypes.string.isRequired,
    expanded: PropTypes.bool.isRequired,
    sort: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    dispatch: PropTypes.func.isRequired,
    isComments: PropTypes.bool
  };

  render() {
    const { dispatch, expanded, order, sort, toggleDropdown, isComments } = this.props;
    let currentSortOption = sortOptions.find(option => option.field === sort);
    // When we sort table, we can have other sortOptions and no need re-render this button
    if (!currentSortOption) {
      currentSortOption = sortOptions[0];
    }

    return (
      <OrderBy
        sortOptions={sortOptions}
        currentSortOption={currentSortOption}
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
            currentSortOption={currentSortOption}
            dispatch={dispatch}
            isComments={isComments}
            />
        </Positioned>
      </OrderBy>
    );
  }

}