import React from "react";

import { connect } from 'react-redux';
import * as TicketActions from "../Actions/FiltersActions";

@connect(state => ({
  TicketsList: state.Tickets.TicketsList,
  dpWindow: state.Application.dpWindow
}))
export default class TicketsListFrame extends React.Component {
  getClasses(dpWindow) {
    let classes = ['ticket-list-frame', 'dp-list-frame'];

    if (dpWindow.get('collapseNav')) {
      classes.push('expanded');
    }
    if (dpWindow.get('expandedSwitcher')) {
      classes.push('shifted');
    }

    return classes.join(' ');
  }

  render() {
    const { TicketsList, dpWindow } = this.props;

    let tickets = TicketsList.TicketsList.map((ticket) => {
      return (
        <div className="ticket">
          <div className="bulk-editing"></div>

          <span className="ticket-id">#{ticket.id}</span>
          <div className="ticket-title">
            <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
            <a href="#">{ticket.subject}</a>
          </div>

          <div className="agent">

            <span className="chat-avatar"></span>
            <span className="agent-name">Nelson Manning</span>

            <span className="org-avatar"></span>
            <span className="agent-org"> BitDefender,CEO</span>

            <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
          </div>

          <div className="ticket-details">
            <span className="ticket-status level-1"><span>1</span></span>
            <span className="assigned-agent" ></span>
            <span className="ticket-department"><i className="fa fa-users"></i></span>
            <span className="ticket-flags"></span>
            <div className="ticket-timer">Last reply: 25m ago</div>
          </div>
        </div>
      );
    });

    return (
      <section className={this.getClasses(dpWindow)}>
        <div className="ticket-list">
          <div className="tickets-control-bar">

            <div className="bulk-edit-control">
              <a href="#">
                <span><i className="fa fa-check"></i></span>
              </a>
            </div>

            <a href="#" className="ticket-control-button">
              <span className="title">Order by:</span>
              <span className="focus">Date</span>
              <span className="down">Asc <i className="fa fa-caret-down"></i></span>
            </a>

            <a href="#" className="ticket-control-button">
              <span className="title">Filter by:</span>
              <span className="focus">12</span>
              <span className="down">Completed <i className="fa fa-caret-down"></i></span>
            </a>

            <a href="#" className="ticket-control-button">
              <span className="title">View:</span>
              <span className="multi">
                List
                <span className="multi-down"><i className="fa fa-caret-down"></i></span>
              </span>
            </a>
          </div>

          {tickets}

        </div>
      </section>
    );
  }
}
