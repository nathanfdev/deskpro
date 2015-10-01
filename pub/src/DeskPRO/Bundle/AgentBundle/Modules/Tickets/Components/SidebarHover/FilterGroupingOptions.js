import React from "react";
import ReactDOM from 'react-dom';

import * as TicketActions from "../../Actions/FiltersActions";
import * as SidebarActions from "../../Actions/SidebarHoverActions";

export default class FilterGroupingOptions extends React.Component {
  constructor(props) {
    super(props);
    this.filter = {
      id: 0,
      title: '',
    }
    
    this.dispatch = props.dispatch;
    this.handleSubmit = this.handleSubmit.bind(this);
  }
  
  handleSubmit(e) {
    e.preventDefault();

    const title = ReactDOM.findDOMNode(this.refs.title).value.trim();
    const grouping = ReactDOM.findDOMNode(this.refs.grouping).value;
    this.dispatch(SidebarActions.hideSidebarHover());
    this.dispatch(TicketActions.loadFilterGroups(this.filter.id, grouping));
    
    ReactDOM.findDOMNode(this.refs.grouping).value = '';
    ReactDOM.findDOMNode(this.refs.title).value = '';
  }
  
  render() {
    let { payload, dispatch } = this.props;
    
    if(payload) {
      this.filter = payload;
    }
    
    return (
      <div className="sidebar-hover-content">
        <div className="sidebar-hover-header">
          <i className="fa fa-tag"></i>&nbsp;{this.filter.title}
        </div>
      
          <form onSubmit={this.handleSubmit}>
          <p>
            <label>Title</label>
            <input type="text" placeholder="Title" ref="title" defaultValue={this.filter.title} />
          </p>

          <p>
            <label>Grouping Options:</label>
            <select ref="grouping">
              <option value="">None</option>
              <option value="department">Department</option>
              <option value="organization">Organization</option>
              <option value="person">Person</option>
              <option value="language">Language</option>
              <option value="urgency">Urgency</option>
              <option value="agent">Agent</option>
              <option value="agent_team">Agent Team</option>
              <option value="waiting_time">Waiting Time</option>
              <option value="all_waiting_time">All Waiting Time</option>
              <option value="open_time">Open Time</option>
            </select>
          </p>
      
          <p>
            <input type="checkbox" /><label>Group by SLA status</label>
          </p>

          <p>
            <input type="submit" className="button" value="Update Filter" />
          </p>
        </form>

        <div className="filter-options">
          <span className="title">Filter Options:</span>
          <ul>
            <li><i className="fa fa-exclamation-circle"></i> <span>Status:</span> Awaiting Agent</li>
            <li><i className="fa fa-calendar-o"></i> <span>Date:</span> Last Week</li>
            <li><i className="fa fa-users"></i> <span>Department:</span> Tech Support</li>
          </ul>
        </div>
      </div>
    );
  }
}
