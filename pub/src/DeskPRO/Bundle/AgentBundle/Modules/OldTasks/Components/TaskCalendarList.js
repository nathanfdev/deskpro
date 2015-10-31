import React from 'react';
import TaskCalendarCard from '../Components/TaskCalendarCard';

const TaskCalendarList = React.createClass({
  propTypes: {
    closeWindow: React.PropTypes.func,
    dayDate: React.PropTypes.object,
    position: React.PropTypes.object,
    tasks: React.PropTypes.array
  },

  mixins: [
    require('react-onclickoutside')
  ],

  handleClickOutside: function() {
    this.props.closeWindow();
  },

  render: function() {
    const { tasks, dayDate, position } = this.props;
    const _this = this;

    return (<div className="dpw--popup-main dpw--popup-main-pointer dpw--popup-main-pointer-top-left calendar-popup"
                 style={{top: position.y, left: position.x}}>
      <div className="dpw--popup-header">{dayDate ? 'Tasks for ' + dayDate.format('MMMM Do') : 'Tasks'}</div>

      <div className="dpw--popup-content">
        <div className="dpw--popup-content-line">
          <div className="dpw--popup-content-full">
            <div className="dpwd-calendar-tasks dpwd-calendar-tasks-flat">
              <ul>
                {tasks ? tasks.map((task) => {
                  return (<TaskCalendarCard key={task.get('id')} task={task}
                            dispatch={_this.props.dispatch.bind(_this)}
                            openHover={_this.props.openHover.bind(_this)}
                            closeHover={_this.props.closeHover.bind(_this)} />);
                }) : ''}
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>);
  }
});

module.exports = TaskCalendarList;
