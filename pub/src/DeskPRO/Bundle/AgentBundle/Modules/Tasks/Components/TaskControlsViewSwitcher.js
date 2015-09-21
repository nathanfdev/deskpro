import React from 'react';

export default class TaskControlsViewSwitcher extends React.Component {
  render() {
    return (<div>
            <ul>
                <li><a href="#" onClick={this.props.setView.bind(this, 'list')}>List</a></li>
                <li><a href="#" onClick={this.props.setView.bind(this, 'kanban')}>Kanban</a></li>
                <li><a href="#" onClick={this.props.setView.bind(this, 'condensed')}>Condensed</a></li>
                <li><a href="#" onClick={this.props.setView.bind(this, 'calendar')}>Calendar</a></li>
            </ul>
        </div>);
  }
}
