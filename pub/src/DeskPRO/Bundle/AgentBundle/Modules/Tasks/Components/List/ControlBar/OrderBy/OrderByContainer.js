import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { OrderByDropdown } from './OrderByDropdown';

const sortOptions = {
  list: { label: 'List', icon: 'list' },
  project: { label: 'Project', icon: 'briefcase' },
  date_due: { label: 'Due Date', icon: 'calendar' },
  date_done: { label: 'Done Date', icon: 'calendar' },
  date_created: { label: 'Created Date', icon: 'calendar' },
  assignee: { label: 'Assignee', icon: 'user' }
};

@connect()
export class OrderByContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
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
    console.log(value);
    this.onCloseDropDown();
  };

  onToggleListOrder = value => {
    console.log(value);
    this.onCloseDropDown();
  };

  render() {
    const currentSortOption = sortOptions[0];
    const order = 'asc';

    return (
      <OrderBy ref="button"
               currentSortOption={currentSortOption}
               sortOptions={sortOptions}
               order={order}
               toggleDropdown={this.onOpenDropdown}>

        <Detached isOpen={this.state.dropdownOpened}
                  positionAt="left bottom"
                  positionTarget={this.refs.button}>

          <ClickOut onClickOut={this.onCloseDropDown}>
            <OrderByDropdown order={order}
                             currentSortOption={currentSortOption}
                             sortOptions={sortOptions}
                             onToggleListSort={this.onToggleListSort}
                             onToggleListOrder={this.onToggleListOrder} />
          </ClickOut>
        </Detached>
      </OrderBy>
    );
  }
}
