import React from "react";

export default class TicketsTabStars extends React.Component {
  render() {
    return (
      <div className="sidebar-list sidebar-list-flags" >
        <div className="sidebar-flag-list">

          <ul>
            <li>
              <div className="list-counter-bucket"><a className="list-counter" href="#">52</a></div>
              <a href="#" className="sidebar-label-list-item"><span className="label-icon"><i className="fa fa-flag"></i></span><span className="label-name">Blue</span></a>
            </li>
            <li>
              <div className="list-counter-bucket"><a className="list-counter" href="#">52</a></div>
              <a href="#" className="sidebar-label-list-item"><span className="label-icon"><i className="fa fa-flag"></i></span><span className="label-name">Red</span></a>
            </li>
            <li>
              <div className="list-counter-bucket"><a className="list-counter" href="#">52</a></div>
              <a href="#" className="sidebar-label-list-item"><span className="label-icon"><i className="fa fa-flag"></i></span><span className="label-name">Green</span></a>
            </li>
            <li>
              <div className="list-counter-bucket"><a className="list-counter" href="#">52</a></div>
              <a href="#" className="sidebar-label-list-item"><span className="label-icon"><i className="fa fa-flag"></i></span><span className="label-name">Another Flag</span></a>
            </li>
            <li>
              <div className="list-counter-bucket"><a className="list-counter" href="#">52</a></div>
              <a href="#" className="sidebar-label-list-item"><span className="label-icon"><i className="fa fa-flag"></i></span><span className="label-name">Excellent</span></a>
            </li>
            <li>
              <div className="list-counter-bucket"><a className="list-counter" href="#">52</a></div>
              <a href="#" className="sidebar-label-list-item"><span className="label-icon"><i className="fa fa-flag"></i></span><span className="label-name">Last Flag</span></a>
            </li>
          </ul>

          <span className="section-info">Flags let you create personal collections of tickets</span>

          <a href="#" className="add-new">Create a new flag</a>
        </div>
      </div>
    );
  }
}
