import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { OrderByDropdown } from './OrderByDropdown';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { currentOrderSelector, currentSortSelector } from '../../../../Selectors/tasks';

const sortOptions = {
  list: { label: 'List', icon: 'list' },
  project: { label: 'Project', icon: 'briefcase' },
  date_due: { label: 'Due Date', icon: 'calendar' },
  date_done: { label: 'Done Date', icon: 'calendar' },
  date_created: { label: 'Created Date', icon: 'calendar' },
  assignee: { label: 'Assignee', icon: 'user' }
};

@connect(state => ({
  currentSort: currentSortSelector(state),
  currentOrder: currentOrderSelector(state)
}))
export class OrderByContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    currentSort: PropTypes.string,
    currentOrder: PropTypes.string
  };

  constructor(props) {
    super(props);

    this.state = {
      dropdownOpened: false
    };
  }

  onOpenDropdown = event => {
    event.preventDefault();
    this.setState({
      dropdownOpened: true
    });
  };

  onCloseDropDown = () => {
    this.setState({
      dropdownOpened: false
    });
  };

  onToggleListSort = value => {
    this.props.dispatch(updateRoutingState('list', 'sort', value));
    this.onCloseDropDown();
  };

  onToggleListOrder = value => {
    this.props.dispatch(updateRoutingState('list', 'order', value));
    this.onCloseDropDown();
  };

  render() {
    const { currentSort, currentOrder } = this.props;

    return (
      <OrderBy ref="button"
               currentSortOption={sortOptions[currentSort]}
               sortOptions={sortOptions}
               order={currentOrder}
               toggleDropdown={this.onOpenDropdown}>

        <Detached isOpen={this.state.dropdownOpened}
                  positionAt="left bottom"
                  positionTarget={this.refs.button}>

          <ClickOut onClickOut={this.onCloseDropDown}>
            <OrderByDropdown currentOrder={currentOrder}
                             currentSort={currentSort}
                             sortOptions={sortOptions}
                             onToggleListSort={this.onToggleListSort}
                             onToggleListOrder={this.onToggleListOrder} />
          </ClickOut>
        </Detached>
      </OrderBy>
    );
  }
}
