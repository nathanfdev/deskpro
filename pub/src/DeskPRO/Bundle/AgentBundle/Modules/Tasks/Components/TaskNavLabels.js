import React from 'react';
import TaskNavItemLabel from '../Components/TaskNavItemLabel';

export default class TasksNavLabels extends React.Component {
  render() {
    const {labelList} = this.props;
    const _this = this;

    return (<section className="sidebar-list sidebar-list-labels tasks-nav-labels">
      <div className="list-sidebar-title">
        Labels
      </div>

      <div className="sidebar-label-list sidebar-list">
        <ul>{labelList.labelCharacters ? labelList.labelCharacters.map((object) => {
          return (<li key={object}>
            <span className="labelCharacter">{object}</span>
            {labelList.labelList[object] ? labelList.labelList[object].map((label) => {
              return <TaskNavItemLabel key={label.label} label={label} filterTasks={_this.props.filterTasks.bind(_this)} />;
            }) : ''}
          </li>);
        }) : ''}</ul>
      </div>
    </section>);
  }
}
