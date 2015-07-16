import React from "react";

export default class TicketsTabLabels extends React.Component {
  render() {
    return (
      <div className="sidebar-list sidebar-list-labels" >
        <div className="list-sidebar-label-view">
          <a href="#" className="active">Count</a>
          <a href="#">Alphabet</a>
          <a href="#">Ipsum</a>
        </div>
        <div className="sidebar-label-list">
          <a href="#" className="item-label">android</a>
          <a href="#" className="item-label">ios</a>
          <a href="#" className="item-label">nexus 6</a>
          <a href="#" className="item-label">android tablet</a>
          <a href="#" className="item-label">update</a>
          <a href="#" className="item-label">patch</a>

          <a href="#" className="add-new">Create a new label</a>
        </div>

      </div>
    );
  }
}
