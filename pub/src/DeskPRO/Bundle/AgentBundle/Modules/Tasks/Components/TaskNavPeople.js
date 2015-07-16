import React from "react";

export default class TasksNavPeople extends React.Component {
    render() {
        return (<section className="tasks-nav-people">
            <div className="list-sidebar-title">People <a href="#" className="title-down"><i
                className="fa fa-caret-down"/></a></div>
                <ul>
                    <li>
                        <div className="list-counter-bucket">
                            <a className="list-counter" href="#"
                               onclick="showFilterOptions(this); return false;">0</a>
                        </div>
                        <a href="#" className="item" onmouseover="toggleCountBucket(this);">
                                        <span className="list-icon"><span
                                            styles={{backgroundImage: 'url(./img/avatar6.png)'}} className="avatar"/></span>
                            Harrison Newman
                        </a>
                    </li>
                    <li>
                        <div className="list-counter-bucket">
                            <a className="list-counter" href="#"
                               onclick="showFilterOptions(this); return false;">0</a>
                        </div>
                        <a href="#" className="item" onmouseover="toggleCountBucket(this);">
                                        <span className="list-icon"><span
                                            styles={{backgroundImage: 'url(./img/avatar5.png)'}} className="avatar"/></span>
                            Tyler Weston
                        </a>
                    </li>
                    <li>
                        <div className="list-counter-bucket">
                            <a className="list-counter" href="#"
                               onclick="showFilterOptions(this); return false;">0</a>
                        </div>
                        <a href="#" className="item" onmouseover="toggleCountBucket(this);">
                                        <span className="list-icon"><span
                                            styles={{backgroundImage: 'url(./img/avatar4.jpg)'}} className="avatar"/></span>
                            Ellis Glover
                        </a>
                    </li>
                    <li>
                        <div className="list-counter-bucket">
                            <a className="list-counter" href="#"
                               onclick="showFilterOptions(this); return false;">0</a>
                        </div>
                        <a href="#" className="item" onmouseover="toggleCountBucket(this);">
                                        <span className="list-icon"><span
                                            styles={{backgroundImage: 'url(./img/avatar3.jpg)'}} className="avatar"/></span>
                            David Benson
                        </a>
                    </li>
                    <li>
                        <div className="list-counter-bucket">
                            <a className="list-counter" href="#"
                               onclick="showFilterOptions(this); return false;">0</a>
                        </div>
                        <a href="#" className="item" onmouseover="toggleCountBucket(this);">
                                        <span className="list-icon"><span
                                            styles={{backgroundImage: 'url(./img/avatar2.png)'}} className="avatar"/></span>
                            Demi Carroll
                        </a>
                    </li>
                </ul>
                </section>);
    }
}