import React from 'react';

const TaskCalendarList = React.createClass({
  mixins: [
    require('react-onclickoutside')
  ],

  handleClickOutside: function(evt) {
    this.props.closeWindow();
  },

  render: function() {
    const { tasks, dayDate, position } = this.props;

    return (<div className="dpmw--popup-main dpw--popup-main-pointer dpw--popup-main-pointer-top-left calendar-popup"
                 style={{top: position.y, left: position.x}}>
      <div className="dpmw--popup-header">{dayDate ? 'Tasks for ' + dayDate.format('MMMM Do') : 'Tasks'}</div>

      <div className="dpw--popup-content">
        <div className="dpw--popup-content-line">
          <div className="dpmw--popup-content-full">
            <div className="dpwd-calendar-tasks dpwd-calendar-tasks-flat">
              <ul>
                {tasks ? tasks.map((task) => {
                  return (<li>
                    <a href="#">{task.title}</a>
                  </li>);
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
