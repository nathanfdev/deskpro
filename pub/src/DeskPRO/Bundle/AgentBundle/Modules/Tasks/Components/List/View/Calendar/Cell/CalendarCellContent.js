import React, { PropTypes } from 'react';
import { CalendarCellDropdown } from './CalendarCellDropdown';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { CalendarCellContentItem } from './CalendarCellContentItem';
import jQuery from 'jquery';

export class CalendarCellContent extends React.Component {

  static propTypes = {
    dayDate: PropTypes.object.isRequired,
    tasks: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      dropdownOpened: false
    };
  }

  onOpenAdditionalDropdown = event => {
    event.preventDefault();
    this.setState({
      dropdownOpened: true
    });
  };

  onCloseAdditionalDropdown = () => {
    this.setState({
      dropdownOpened: false
    });
  };

  render() {
    const { tasks } = this.props;
    const shortList = tasks.slice(0, 2);
    const additionalList = tasks.slice(2);

    return (
      <div>
        <ul>
          {shortList.map(task => <CalendarCellContentItem key={task.get('id')}
                                                          task={task} />)}
          {additionalList.count() > 0 &&
            <li>
              <a href="#"
                 ref="button"
                 className="dpwd-calendar-tasks-show-more"
                 onClick={this.onOpenAdditionalDropdown}>

                + {additionalList.count()} tasks <i className="fa fa-sort" />
              </a>
            </li>
          }
        </ul>

        <Detached isOpen={this.state.dropdownOpened}
                  positionTarget={this.refs.button}
                  positionAt="left bottom+5">

          <ClickOut onClickOut={this.onCloseAdditionalDropdown}
                    additionalNodes={additionalList.map(task => `.calendar-task-card-${task.get('id')}`).toArray()}>

            <CalendarCellDropdown>
              {additionalList.map(task => <CalendarCellContentItem key={task.get('id')}
                                                                   task={task} />)}
            </CalendarCellDropdown>
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
