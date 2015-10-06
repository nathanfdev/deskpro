import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { OrderByDropdownContainer } from './OrderByDropdownContainer';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { sortingDataSelector } from '../../../Selectors/list';

@connect(state => ({
  sortOptions: state.Feedback.list.get('sortOptions').toJS(),
  order: state.Feedback.list.get('order'),
  currentSortMode: sortingDataSelector(state)
}))

export class OrderByContainer extends Component {

  static propTypes = {
    expanded: PropTypes.bool.isRequired,
    offset: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  };

  renderDropdown() {
    const {expanded, offset, toggleDropdown} = this.props;
    if (expanded) {
      return (
        <OrderByDropdownContainer offset={offset} toggleDropdown={toggleDropdown}/>
      );
    }
  }

  render() {
    const { sortOptions, order, currentSortMode, toggleDropdown } = this.props;
    return (
      <OrderBy
        sortOptions={sortOptions}
        currentSortMode={currentSortMode}
        order={order}
        toggleDropdown={toggleDropdown}
        >
        {this.renderDropdown()}
      </OrderBy>
    );
  }

}