import React from "react";

export default class TasksNavFrame extends React.Component {
  render() {
    return (<section className="task-nav-frame">
      <div className="sidebar-wrapper" id="sidebar-wrapper">
        <a className="collapse-button" href="#" onclick="resizePanels('hide_filters');"><i className="fa fa-angle-right"></i></a>

        <span className="collapse-controls" onclick="resizePanels('hide_filters');">
          <span className="disc"></span>
          <span className="disc"></span>
          <i className="fa fa-caret-right"></i>
          <span className="disc"></span>
          <span className="disc"></span>
        </span>

        <aside className="sidebar has-tabs" id="sidebar">

          <div className="sidebar-title">
            <span className="sidebar-type-icon">
              <i className="fa fa-envelope-o"></i>
              <span className="help"><i className="fa fa-question"></i></span>
            </span>
            <h1>Tasks</h1>
            <hr/>
              <a href="#" className="slider-control"></a>
            </div>

            <div className="sidebar-list sidebar-list-filters">
              <div className="list-sidebar-title">Tasks <a href="#" className="title-down"><i className="fa fa-caret-down"></i></a></div>
                <ul>
                  <li>
                    <div className="list-counter-bucket">
                      <a className="list-counter-dropdown active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                      <a className="list-counter" href="#" onclick="showFilterOptions(this); return false;">12</a>
                    </div>
                    <a href="#" className="item" onmouseover="toggleCountBucket(this);">My Tasks</a>
                  </li>

                  <li>
                    <div className="list-counter-bucket">
                      <a className="list-counter active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                      <a href="#" className="list-counter" onclick="showFilterOptions(this); return false;">34</a>
                    </div>
                    <a href="#" className="item">My Team Tasks</a>
                  </li>

                  <li>
                    <div className="list-counter-bucket">
                      <a className="list-counter active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                      <a href="#" className="list-counter" onclick="showFilterOptions(this); return false;">9</a>
                    </div>
                    <a href="#" className="item">Delegated Tasks</a>
                  </li>

                  <li>
                    <div className="list-counter-bucket">
                      <a className="list-counter active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                      <a href="#" className="list-counter" onclick="showFilterOptions(this); return false;">132</a>
                    </div>
                    <a href="#" className="item">Unassigned Tasks</a>
                  </li>

                  <li>
                    <div className="list-counter-bucket">
                      <a className="list-counter active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                      <a href="#" className="list-counter" onclick="showFilterOptions(this); return false;">132</a>
                    </div>
                    <a href="#" className="item">All Tasks</a>
                  </li>
                </ul>

              <div className="list-sidebar-title">Projects <a href="#" className="title-down"><i className="fa fa-caret-down"></i></a></div>
              <ul>
                <li>
                  <div className="list-counter-bucket">
                    <a className="list-counter-dropdown active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                    <a className="list-counter" href="#" onclick="showFilterOptions(this); return false;">12</a>
                  </div>
                  <a href="#" className="item" onmouseover="toggleCountBucket(this);"><i className="fa fa-book"></i> Project name</a>
                </li>
              </ul>

              <div className="list-sidebar-title">People <a href="#" className="title-down"><i className="fa fa-caret-down"></i></a></div>
              <ul>
                <li>
                  <div className="list-counter-bucket">
                    <a className="list-counter-dropdown active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                    <a className="list-counter" href="#" onclick="showFilterOptions(this); return false;">12</a>
                  </div>
                  <a href="#" className="item" onmouseover="toggleCountBucket(this);">
                    <span className="list-icon"><span styles={{backgroundImage: 'url(./img/avatar6.png)'}} className="avatar"></span></span>
                    Harrison Newman
                  </a>
                </li>
                <li>
                  <div className="list-counter-bucket">
                    <a className="list-counter-dropdown active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                    <a className="list-counter" href="#" onclick="showFilterOptions(this); return false;">12</a>
                  </div>
                  <a href="#" className="item" onmouseover="toggleCountBucket(this);">
                    <span className="list-icon"><span styles={{backgroundImage: 'url(./img/avatar5.png)'}} className="avatar"></span></span>
                    Tyler Weston
                  </a>
                </li>
                <li>
                  <div className="list-counter-bucket">
                    <a className="list-counter-dropdown active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                    <a className="list-counter" href="#" onclick="showFilterOptions(this); return false;">12</a>
                  </div>
                  <a href="#" className="item" onmouseover="toggleCountBucket(this);">
                    <span className="list-icon"><span styles={{backgroundImage: 'url(./img/avatar4.jpg)'}} className="avatar"></span></span>
                    Ellis Glover
                  </a>
                </li>
                <li>
                  <div className="list-counter-bucket">
                    <a className="list-counter-dropdown active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                    <a className="list-counter" href="#" onclick="showFilterOptions(this); return false;">12</a>
                  </div>
                  <a href="#" className="item" onmouseover="toggleCountBucket(this);">
                    <span className="list-icon"><span styles={{backgroundImage: 'url(./img/avatar3.jpg)'}} className="avatar"></span></span>
                    David Benson
                  </a>
                </li>
                <li>
                  <div className="list-counter-bucket">
                    <a className="list-counter-dropdown active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                    <a className="list-counter" href="#" onclick="showFilterOptions(this); return false;">12</a>
                  </div>
                  <a href="#" className="item" onmouseover="toggleCountBucket(this);">
                    <span className="list-icon"><span styles={{backgroundImage: 'url(./img/avatar2.png)'}} className="avatar"></span></span>
                    Demi Carroll
                  </a>
                </li>
              </ul>

                <div className="list-sidebar-title">
                  Labels <a href="#" className="title-down"><i className="fa fa-caret-down"></i></a>
                </div>

                <ul>
                  <li>
                    <div className="list-counter-bucket">
                      <a className="list-counter active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                      <a href="#" className="list-counter" onclick="showFilterOptions(this); return false;">26</a>
                    </div>
                    <a href="#" className="item"><i className="fa fa-tag"></i> Label</a>
                  </li>
                  <li>
                    <div className="list-counter-bucket">
                      <a className="list-counter active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                      <a href="#" className="list-counter" onclick="showFilterOptions(this); return false;">26</a>
                    </div>
                    <a href="#" className="item"><i className="fa fa-tag"></i> Label</a>
                  </li>
                  <li>
                    <div className="list-counter-bucket">
                      <a className="list-counter active" onclick="showFilterOptions(this); return false;" href="#" styles={{display: 'none'}}>&nbsp;<i className="fa fa-angle-down"></i></a>
                      <a href="#" className="list-counter" onclick="showFilterOptions(this); return false;">26</a>
                    </div>
                    <a href="#" className="item"><i className="fa fa-tag"></i> Label</a>
                  </li>
                </ul>

              </div>
            </aside>
          </div>
    </section>);
  }
}
