import React from "react";

import TicketsTabFilterSetItem from "./TicketsTabFilterSetItem";

export default class TicketsTabFilterSets extends React.Component {
  render() {
    const { filterSetsList, filterSetsCounts, loadFilterTickets, dispatch } = this.props;

    const filtersets = filterSetsList.FilterSetsList.map(filter_set => {
      let total = 0;
      let my_filter_counts = null;
      if(typeof filterSetsCounts.FilterSetsCounts !== 'undefined') {
        for(let k in filterSetsCounts.FilterSetsCounts) {
          if(filterSetsCounts.FilterSetsCounts[k].group == filter_set.id) {
            total = filterSetsCounts.FilterSetsCounts[k].count;
            my_filter_counts = filterSetsCounts.FilterSetsCounts[k].nested;
            break;
          }
        }
      }
      return (
        <TicketsTabFilterSetItem
          filterSetsList={filterSetsList}
          filterSetsCounts={filterSetsCounts}
          loadFilterTickets={loadFilterTickets}
          filterSet={filter_set}
          totalTickets={total}
          filterCounts={my_filter_counts}
          {...this.props} />
      );
    });

    return (
      <div className="sidebar-list sidebar-list-filters">

        {filtersets}

        <ul>
          <li className="counter-display">
            <div className="list-counter-bucket">
              <a className="list-counter-dropdown active" onclick="showFilterOptions(this); return false;" href="#">&nbsp;<i className="fa fa-angle-down"></i></a>
              <a href="#" className="list-counter active" onclick="showFilterOptions(this); return false;">132</a>
            </div>
            <a href="#" className="item" onmouseover="toggleCountBucket(this);">My Tickets</a>

            <ul className="with-connectors">
              <li>
                <div className="list-counter-bucket"><a href="#" className="list-counter">4</a></div>
                <a href="#" className="item active">Recent Tickets</a>
              </li>
              <li>
                <div className="list-counter-bucket">
                  <a href="#" className="list-counter active" onclick="showFilterOptions(this); return false;">132</a>
                </div>
                <a href="#" className="item">Not As Recent Tickets</a>
              </li>
              <li>
                <div className="list-counter-bucket"><a href="#" className="list-counter">101</a></div>
                <a href="#" className="item"><i className="fa fa-send"></i> Really Not Recent</a>
              </li>
            </ul>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#" className="list-counter">2</a></div>
            <a href="#" className="item">Tickets I Follow</a>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#" className="list-counter">52</a></div>
            <a href="#" className="item">My Teams
              <div className="sla">
                <span className="sla-green">22</span>
                <span className="sla-yellow selected">12</span>
                <span className="sla-red">5</span>
              </div>
            </a>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#" className="list-counter">3</a></div>
            <a href="#" className="item">Unassigned Tickets</a>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#" className="list-counter">3</a></div>
            <a href="#" className="item">Tickets by User</a>

            <ul className="with-connectors">
              <li>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
                <a href="#" className="item">
                  <span className="list-icon"><span className="avatar" ></span></span>
                  Sophie Norton
                  <div className="sla">
                    <span className="sla-green selected">2</span>
                    <span className="sla-yellow">0</span>
                    <span className="sla-red">4</span>
                  </div>
                </a>
              </li>

              <li>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
                <a href="#" className="item">
                  <span className="list-icon"><span className="avatar" ></span></span>
                  Nelson Manning
                  <div className="sla">
                    <span className="sla-green">22</span>
                    <span className="sla-yellow selected">12</span>
                    <span className="sla-red">5</span>
                  </div>
                </a>
              </li>
            </ul>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#counter" className="list-counter">1028</a></div>
            <a href="#" className="item">All Tickets</a>
          </li>
        </ul>

        <div className="list-sidebar-title">Secondary Tickets</div>
        <ul>
          <li>
            <div className="list-counter-bucket"><a href="#counter" className="list-counter">12</a></div>
            <a href="#" className="item">Mine On Hold</a>


            <ul className="with-connectors">
              <li>
                <div className="list-counter-bucket"><a href="#counter" className="list-counter">12</a></div>
                <a href="#" className="item"><i className="fa fa-clock-o"></i> And an icon</a>
              </li>
            </ul>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#counter" className="list-counter">12</a></div>
            <a href="#" className="item">All On Hold</a>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#counter" className="list-counter">12</a></div>
            <a href="#" className="item">My Recent Activity</a>

            <ul className="with-connectors">
              <li>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>

                <a className="item" href="#">
                  <span className="list-icon">
                    <span className="status-icon">A</span>
                  </span>
                  Awaiting Agent
                </a>

                <ul className="with-connectors">
                  <li>
                    <div className="list-counter-bucket"><a href="#" className="list-counter faded">12</a></div>
                    <a href="#" className="item">Awaiting Vendor</a>
                  </li>

                  <li>
                    <div className="list-counter-bucket"><a href="#" className="list-counter faded">12</a></div>
                    <a href="#" className="item">Bug to Fix</a>
                  </li>
                </ul>

              </li>

              <li>
                <div className="list-counter-bucket"><a className="list-counter" href="#">12</a></div>

                <a className="item" href="#">
                  <span className="list-icon">
                    <span className="status-icon">A</span>
                  </span>
                  On Hold
                </a>
              </li>

              <li>
                <div className="list-counter-bucket"><a className="list-counter" href="#">12</a></div>

                <a className="item" href="#">
                  <span className="list-icon">
                    <span className="status-icon">A</span>
                  </span>
                  Awaiting User
                </a>
              </li>
            </ul>

          </li>

          <li>
            <div className="list-counter-bucket"><a href="#" className="list-counter">12</a></div>
            <a href="#" className="item">Ageing</a>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#" className="list-counter">12</a></div>
            <a href="#" className="item">Urgent!</a>

            <div className="sidebar-urgent-sliders">

              <div className="slider level-1">
                <div className="slider-container">
                  <span className="slider-grabber-wrapper"><span className="slider-grabber">1</span></span>
                </div>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
              </div>

              <div className="slider level-2">
                <div className="slider-container">
                  <span className="slider-grabber-wrapper"><span className="slider-grabber">2</span></span>
                </div>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
              </div>

              <div className="slider level-3">
                <div className="slider-container">
                  <span className="slider-grabber-wrapper"><span className="slider-grabber">3</span></span>
                </div>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
              </div>

              <div className="slider level-4">
                <div className="slider-container">
                  <span className="slider-grabber-wrapper"><span className="slider-grabber">4</span></span>
                </div>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
              </div>

              <div className="slider level-5">
                <div className="slider-container">
                  <span className="slider-grabber-wrapper"><span className="slider-grabber">5</span></span>
                </div>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
              </div>

              <div className="slider level-6">
                <div className="slider-container">
                  <span className="slider-grabber-wrapper"><span className="slider-grabber">6</span></span>
                </div>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
              </div>

              <div className="slider level-7">
                <div className="slider-container">
                  <span className="slider-grabber-wrapper"><span className="slider-grabber">7</span></span>
                </div>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
              </div>

              <div className="slider level-8">
                <div className="slider-container">
                  <span className="slider-grabber-wrapper"><span className="slider-grabber">8</span></span>
                </div>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
              </div>

              <div className="slider level-9">
                <div className="slider-container">
                  <span className="slider-grabber-wrapper"><span className="slider-grabber">9</span></span>
                </div>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
              </div>

              <div className="slider level-10">
                <div className="slider-container">
                  <span className="slider-grabber-wrapper"><span className="slider-grabber">10</span></span>
                </div>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
              </div>
            </div>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#counter" className="list-counter">12</a></div>
            <a href="#" className="item">New (Opened Today)</a>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#counter" className="list-counter">12</a></div>
            <a href="#" className="item">My Awaiting User</a>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#counter" className="list-counter">12</a></div>
            <a href="#" className="item">All Awaiting User</a>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#counter" className="list-counter">12</a></div>
            <a href="#" className="item">By Language</a>

            <ul className="with-connectors">
              <li>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
                <a className="item" href="#">
                  <span className="list-icon flag"><span className="status-icon flag" ></span></span>
                  English
                </a>
              </li>
              <li>
                <div className="list-counter-bucket"><a className="list-counter" href="#counter">12</a></div>
                <a className="item" href="#">
                  <span className="list-icon flag"><span className="status-icon flag" ></span></span>
                  Pirate
                </a>
              </li>
            </ul>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#counter" className="list-counter">12</a></div>
            <a href="#" className="item">Resolved</a>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#counter" className="list-counter">12</a></div>
            <a href="#" className="item">Spam</a>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#counter" className="list-counter">12</a></div>
            <a href="#" className="item">Deleted</a>
          </li>

          <li>
            <div className="list-counter-bucket"><a href="#counter" className="list-counter">12</a></div>
            <a href="#" className="item">Awaiting Validation</a>
          </li>
        </ul>

      </div>
    );
  }
}
