import React from "react";

export default class TicketsListFrame extends React.Component {
    render() {
        return (<section className="task-list-frame">

                <div className="ticket-list">

                    <div className="tickets-control-bar">

                        <div className="bulk-edit-control">
                            <a href="#">
                                <span className="checkbox"><i className="fa fa-check" /></span>
                            </a>
                            <span className="count" style={{display: "none"}}><span>14</span></span>
                        </div>

                        <span className="ticket-controls-default">
                            <a href="#" className="ticket-control-button">
                                <span className="title">Order by:</span>
                                <span className="focus">Date</span>
                                <span className="down">Asc <i className="fa fa-caret-down" /></span>
                            </a>

                            <a href="#" className="ticket-control-button">
                                <span className="title">Filter by:</span>
                                <span className="focus">12</span>
                                <span className="down">Completed <i className="fa fa-caret-down" /></span>
                            </a>

                            <a href="#" className="ticket-control-button">
                                <span className="title">View:</span>
                                <span className="multi">
                                    List
                                    <span className="multi-down"><i className="fa fa-caret-down" /></span>
                                </span>
                            </a>
                        </span>

                        <span className="ticket-controls-bulk-editing">
                            <a href="#">
                                <span>Assign</span>
                                <hr />
                                <i className="fa fa-caret-down" />
                            </a>

                            <a href="#">
                                <span>Statuses</span>
                                <hr />
                                <i className="fa fa-caret-down" />
                            </a>

                            <a href="#">
                                <span>Macros</span>
                                <hr />
                                <i className="fa fa-caret-down" />
                            </a>

                            <a href="#">
                                <span><i className="fa fa-reply" /></span>
                                <hr />
                                <i className="fa fa-caret-down" />
                            </a>

                            <a href="#">
                                <span><i className="fa fa-asterisk" /></span>
                                <hr />
                                <i className="fa fa-caret-down" />
                            </a>

                            <hr />

                            <a href="#" className="active">
                                <span>GO</span>
                            </a>

                            <a href="#" className="cancel">
                                <span>Cancel</span>
                            </a>
                        </span>
                    </div>

                    <div className="card">
                        <div className="card-status-bar status-bar-left level-1"></div>
                        <div className="card-status-bar status-bar-right level-1"></div>

                        <div className="card-checkbox">
                            <span className="checkbox"><i className="fa fa-check" /></span>
                        </div>

                        <div className="card-status">
                            U.
                        </div>

                        <div className="top-right-box">
                            <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar6.png)"}}></span>
                            <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar5.png)"}}></span>
                            <span className="ticket-department"><i className="fa fa-users" /></span>
                        </div>

                        <div className="card-line">
                            <span className="line-box">
                                <i className="fa fa-star" />
                            </span>
                            <h1>Print this page to PDF for the complete set of vector. Print this page to PDF for the complete set of vector. Print this page to PDF for the complete set of vector.</h1>
                            <div className="ticket-label-box">
                                <span className="ticket-label">Bug</span>
                                <span className="ticket-label">Ipsum</span>
                            </div>
                        </div>

                        <div className="card-line">
                            <span className="line-box">
                                <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar3.jpg)"}}></span>
                            </span>
                            <div className="person">
                                <span className="agent">Carlton Bush</span>
                                <span className="email">carlton@freshprice.com</span>
                            </div>

                            <div className="ticket-timer">SLA: <span className="sla-yellow">25m</span> <span className="sla-red">4h33m</span> </div>
                        </div>

                        <div className="card-line">
                            <div className="ticket-extras">
                                <span>Assigned to: <a href="#"><span className="chat-avatar" style={{backgroundImage: "url(./img/avatar6.png)"}}></span>Shawn Butler</a></span>
                                <span><i className="fa fa-clock-o" /> Created 25m ago</span>
                            </div>
                        </div>
                    </div>

                    <div className="card">
                        <div className="card-status-bar status-bar-left level-5"></div>
                        <div className="card-status-bar status-bar-right level-5"></div>

                        <div className="card-checkbox">
                            <span className="checkbox"><i className="fa fa-check" /></span>
                        </div>

                        <div className="card-status">
                            U.
                        </div>

                        <div className="top-right-box">
                            <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar6.png)"}}></span>
                            <span className="ticket-department"><i className="fa fa-users" /></span>
                        </div>


                        <div className="card-line">
                            <span className="line-box">
                                <i className="fa fa-star" />
                            </span>
                            <h1>Print this page to PDF for the complete set of vector. Print this page to PDF for the complete set of vector. Print this page to PDF for the complete set of vector.</h1>
                        </div>

                        <div className="card-line">
                            <span className="line-box">
                                <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar3.jpg)"}}></span>
                            </span>
                            <div className="person">
                                <span className="agent">Carlton Bush</span>
                                <span className="email">carlton@freshprice.com</span>
                            </div>

                            <div className="ticket-timer">SLA: <span className="sla-yellow">25m</span> <span className="sla-red">4h33m</span> </div>
                        </div>

                        <div className="card-line">
                            <div className="ticket-intro">
                                <p>I'm trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (it's the client's wish, and I can't change that.)</p>
                            </div>
                        </div>
                    </div>

                    <div className="card moved" />

                    <div className="card moving">
                        <div className="card-status-bar status-bar-left level-1"></div>
                        <div className="card-status-bar status-bar-right level-1"></div>

                        <div className="card-checkbox">
                            <span className="checkbox"><i className="fa fa-check" /></span>
                        </div>

                        <div className="card-status">
                            U.
                        </div>

                        <div className="top-right-box">
                            <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar6.png)"}}></span>
                            <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar5.png)"}}></span>
                            <span className="ticket-department"><i className="fa fa-users" /></span>
                        </div>


                        <div className="card-line">
                            <span className="line-box">
                                <i className="fa fa-star" />
                            </span>
                            <h1>Print this page to PDF for the complete set of vector. Print this page to PDF for the complete set of vector. Print this page to PDF for the complete set of vector.</h1>
                            <div className="ticket-label-box">
                                <span className="ticket-label">Bug</span>
                                <span className="ticket-label">Ipsum</span>
                            </div>
                        </div>

                        <div className="card-line">
                            <span className="line-box">
                                <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar3.jpg)"}}></span>
                            </span>
                            <div className="person">
                                <span className="agent">Carlton Bush</span>
                                <span className="email">carlton@freshprice.com</span>
                            </div>

                            <div className="ticket-timer">SLA: <span className="sla-yellow">25m</span> <span className="sla-red">4h33m</span> </div>
                        </div>


                        <div className="card-line">
                            <div className="ticket-extras">
                                <span>Assigned to: <a href="#"><span className="chat-avatar" style={{backgroundImage: "url(./img/avatar6.png)"}}></span>Shawn Butler</a></span>
                                <span><i className="fa fa-clock-o" /> Created 25m ago</span>
                            </div>
                        </div>
                    </div>

                    <div className="card feedback-card">
                        <div className="card-status-bar status-bar-left level-8"></div>
                        <div className="card-status-bar status-bar-right level-8"></div>

                        <div className="card-checkbox">
                            <span className="checkbox"><i className="fa fa-check" /></span>
                        </div>

                        <div className="top-right-box">
                            <span className="text">Carlton Bush</span> <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar6.png)"}}></span>
                        </div>


                        <div className="card-line">
                            <span className="line-box card-feedback-mark">
                                <i className="fa fa-thumbs-up" /><span className="feedback-count">12</span>
                            </span>
                            <h1>Feedback card item lorel ipsum.</h1>
                        </div>

                        <div className="card-line">
                            <div className="ticket-intro">
                                <p>I'm trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (it's the client's wish, and I can't change that.)</p>
                            </div>
                        </div>
                    </div>

                    <div className="card move-target" />

                    <div className="card task-card">
                        <div className="card-status-bar status-bar-left"></div>
                        <div className="card-status-bar status-bar-right"></div>

                        <div className="card-checkbox">
                            <span className="checkbox"><i className="fa fa-check" /></span>
                        </div>

                        <div className="top-right-box">
                            <span className="text">Carlton Bush</span> <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar6.png)"}}></span>
                        </div>


                        <div className="card-line">
                            <span className="line-box card-task-mark">
                                Mark Done
                            </span>
                            <h1>Feedback card item lorel ipsum.</h1>
                        </div>

                        <div className="card-line">
                            <div className="task-extras">
                                <div>5 <i className="fa fa-comment" /></div>
                                <span className="disc"></span>
                                <div>1/3 <i className="fa fa-folder-open" /></div>
                            </div>

                            <div className="task-properties">
                                <div>
                                    <i className="fa fa-calendar-o" /> Due: Today, 2.30PM
                                </div>

                                <span className="disc"></span>

                                <div>
                                    <i className="fa fa-book" /> Bug Fixes
                                </div>

                                <span className="disc"></span>

                                <div>
                                    <i className="fa fa-link" /> <a href="#">Linked ticket</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="card task-card task-card-completed">

                        <div className="card-checkbox">
                            <span className="checkbox"><i className="fa fa-check" /></span>
                        </div>

                        <div className="top-right-box">
                            <span className="text">Carlton Bush</span> <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar6.png)"}}></span>
                        </div>

                        <div className="card-line">
                            <h1>Feedback card item lorel ipsum.</h1>
                        </div>

                        <div className="card-line">
                            <div className="task-extras">
                                <div>5 <i className="fa fa-comment" /></div>
                                <span className="disc"></span>
                                <div>1/3 <i className="fa fa-folder-open" /></div>
                            </div>

                            <div className="task-properties">
                                <span className="line-box card-task-mark">Done <i className="fa fa-check" /></span>

                                <div>
                                    <i className="fa fa-calendar-o" /> Due: Today, 2.30PM
                                </div>

                                <span className="disc"></span>

                                <div>
                                    <i className="fa fa-book" /> Bug Fixes
                                </div>

                                <span className="disc"></span>

                                <div>
                                    <i className="fa fa-link" /> <a href="#">Linked ticket</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="card task-card">

                        <div className="card-checkbox">
                            <span className="checkbox"><i className="fa fa-check" /></span>
                        </div>

                        <div className="top-right-box">
                            <span className="text">Carlton Bush</span> <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar6.png)"}}></span>
                        </div>


                        <div className="card-line">
                            <h1>Feedback card item lorel ipsum.</h1>
                        </div>

                        <div className="card-line">
                            <div className="task-extras">
                                <div>5 <i className="fa fa-comment" /></div>
                                <span className="disc"></span>
                                <div>1/3 <i className="fa fa-folder-open" /></div>
                            </div>

                            <div className="task-properties">
                                <span className="line-box card-task-mark offset">Mark Done</span>

                                <div>
                                    <i className="fa fa-calendar-o" /> Due: Today, 2.30PM
                                </div>

                                <span className="disc"></span>

                                <div>
                                    <i className="fa fa-book" /> Bug Fixes
                                </div>

                                <span className="disc"></span>

                                <div>
                                    <i className="fa fa-link" /> <a href="#">Linked ticket</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="card task-card bulk-editing">

                        <div className="card-checkbox-large">
                            <span className="checkbox"><i className="fa fa-check" /></span>
                        </div>

                        <div className="card-checkbox">
                            <span className="checkbox"><i className="fa fa-check" /></span>
                        </div>

                        <div className="top-right-box">
                            <span className="text">Carlton Bush</span> <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar6.png)"}}></span>
                        </div>


                        <div className="card-line">
                            <h1>Feedback card item lorel ipsum.</h1>
                        </div>

                        <div className="card-line">
                            <div className="task-extras">
                                <div>5 <i className="fa fa-comment" /></div>
                                <span className="disc"></span>
                                <div>1/3 <i className="fa fa-folder-open" /></div>
                            </div>

                            <div className="task-properties">
                                <span className="line-box card-task-mark offset">Mark Done</span>

                                <div>
                                    <i className="fa fa-calendar-o" /> Due: Today, 2.30PM
                                </div>

                                <span className="disc"></span>

                                <div>
                                    <i className="fa fa-book" /> Bug Fixes
                                </div>

                                <span className="disc"></span>

                                <div>
                                    <i className="fa fa-link" /> <a href="#">Linked ticket</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="card task-card bulk-editing selected">

                        <div className="card-checkbox-large">
                            <span className="checkbox checked"><i className="fa fa-check" /></span>
                        </div>

                        <div className="card-checkbox">
                            <span className="checkbox"><i className="fa fa-check" /></span>
                        </div>

                        <div className="top-right-box">
                            <span className="text">Carlton Bush</span> <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar6.png)"}}></span>
                        </div>


                        <div className="card-line">
                            <h1>Feedback card item lorel ipsum.</h1>
                        </div>

                        <div className="card-line">
                            <div className="task-extras">
                                <div>5 <i className="fa fa-comment" /></div>
                                <span className="disc"></span>
                                <div>1/3 <i className="fa fa-folder-open" /></div>
                            </div>

                            <div className="task-properties">
                                <span className="line-box card-task-mark offset">Mark Done</span>

                                <div>
                                    <i className="fa fa-calendar-o" /> Due: Today, 2.30PM
                                </div>

                                <span className="disc"></span>

                                <div>
                                    <i className="fa fa-book" /> Bug Fixes
                                </div>

                                <span className="disc"></span>

                                <div>
                                    <i className="fa fa-link" /> <a href="#">Linked ticket</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="ticket-hover-info-panel">
                        <div className="panel-content">
                            <h1>Manage installation Settings</h1>
                            <div className="person-info">
                                <span className="chat-avatar" style={{backgroundImage: "url(./img/avatar6.png)"}}></span>
                                <span className="agent">Carlton Bush</span>
                                <span className="email">carlton@freshprice.com</span>
                                <span className="disc"></span>
                                <span className="time">2 hours ago</span>
                            </div>

                            <p>Print this page to PDF for the complete set of vectors. Or to use on the desktop, install FontAwesome.otf, set it as the font in your application, and copy and paste the icons (not the unicode) directly from this page into your designs.</p>
                        </div>

                        <div className="panel-footer">
                            <ul>
                                <li><a href="#">Assign Me</a></li>
                                <li><a href="#">Unassign</a></li>
                                <li><a href="#">Assign Agent <i className="fa fa-caret-down" /></a></li>
                                <li><a href="#">Assign Team <i className="fa fa-caret-down" /></a></li>
                                <li><a href="#">Set Awaiting User</a></li>
                                <li><a href="#">Set Resolved</a></li>
                            </ul>
                        </div>

                    </div>
                </div>
        </section>);
    }
}
