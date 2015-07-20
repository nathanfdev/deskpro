import React from "react";

export default class FilterGroupingOptions extends React.Component {
  render() {
    return (
      <div className="sidebar-hover-content">
        <div className="sidebar-hover-header">
          <i className="fa fa-tag"></i>&nbsp;My Tickets: <span>By User</span>
        </div>
      
        <p>
          <label>Title</label>
          <input type="text" placeholder="Title" />
        </p>

        <p>
          <label>Grouping Options:</label>
          <select>
            <option>Department</option>
            <option>Product</option>
            <option>Workflow</option>
            <option>Organization</option>
            <option>Person</option>
            <option>Language</option>
            <option>Department</option>
            <option>Urgency</option>
            <option>Waiting Time</option>
            <option>All Waiting Time</option>
            <option>Open Time</option>
            <option>Size of your Organization</option>
          </select>
        </p>
      
        <p>
          <input type="checkbox" /><label>Group by SLA status</label>
        </p>

        <p>
          <button class="btn btn-primary">Update Filter</button>
        </p>

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
