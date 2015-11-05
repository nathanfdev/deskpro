import React from 'react';
import { connect } from 'react-redux';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { OrderByDropdown } from './OrderByDropdown';

const sortOptions = [
  { field: 'list', label: 'List', icon: 'list' },
  { field: 'project', label: 'Project', icon: 'briefcase' },
  { field: 'date_due', label: 'Due Date', icon: 'calendar' },
  { field: 'date_done', label: 'Done Date', icon: 'calendar' },
  { field: 'date_created', label: 'Created Date', icon: 'calendar' },
  { field: 'assignee', label: 'Assignee', icon: 'user' }
];

@connect()
export class OrderByContainer extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      dropdownOpened: false
    };
  }

  onOpenDropdown = () => {
    this.setState({
      dropdownOpened: true
    });
  };

  onCloseDropDown = () => {
    this.setState({
      dropdownOpened: false
    });
  };

  render() {
    return (
      <OrderBy ref="button"
               sortOptions={sortOptions}
               toggleDropdown={this.onOpenDropdown}>

        <Detached isOpen={this.state.dropdownOpened}
                  positionAt="left bottom"
                  positionTarget={this.refs.button}>

          <ClickOut onClickOut={this.onCloseDropDown}>
            <OrderByDropdown sortOptions={sortOptions} />
          </ClickOut>
        </Detached>
      </OrderBy>
    );
  }
}
