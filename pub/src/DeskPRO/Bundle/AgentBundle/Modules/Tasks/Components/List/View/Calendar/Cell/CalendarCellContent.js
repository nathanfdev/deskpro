import React, { PropTypes } from 'react';
import { CalendarCellDropdown } from './CalendarCellDropdown';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { CalendarCellContentItem } from './CalendarCellContentItem';
import moment from 'moment';

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
    const { tasks, dayDate } = this.props;

    const dayTasks = tasks.filter(task => dayDate.isSame(moment(task.get('date_due')), 'day'));
    const shortList = dayTasks.slice(0, 2);
    const additionalList = dayTasks.slice(2);

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
                    additionalNodes={['.calendar-task-card', '.assign-form']}>

            <CalendarCellDropdown dayDate={dayDate}>
              {additionalList.map(task => <CalendarCellContentItem key={task.get('id')}
                                                                   task={task} />)}
            </CalendarCellDropdown>
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
