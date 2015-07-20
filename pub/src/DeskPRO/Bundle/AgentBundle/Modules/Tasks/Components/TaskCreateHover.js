import React from "react";

import * as TaskActions from "../Actions/TaskListActions";

export default class TaskCreateHover extends React.Component {

    render() {
        return (<div className="sidebar-hover" style={{top: '152px'}}>

            <div className="sidebar-hover-content">
                <div className="sidebar-hover-header">
                    <i className="fa fa-tags"></i> <span className="title"><span>Inbox -</span> My Tickets</span>
                </div>

                <div className="sidebar-hover-content-box">
                    <h2>Title</h2>
                    <input type="text" placeholder="Title" />
                </div>

                <div className="sidebar-hover-content-box">
                    <h2>Grouping Options</h2>
                    <span className="select-dropdown">
                        <span className="current-option" onclick="toggleSelect(this); return false;">Workflow <i className="fa fa-caret-down"/></span>
                        <div className="select-options">
                            <ul>
                                <li><a href="#">Department</a></li>
                                <li><a href="#">Product</a></li>
                                <li><a href="#">Workflow</a></li>
                                <li><a href="#">Organization</a></li>
                                <li><a href="#">Person</a></li>
                                <li><a href="#">Language</a></li>
                                <li><a href="#">Department</a></li>
                                <li><a href="#">Urgency</a></li>
                                <li><a href="#">Waiting Time</a></li>
                                <li><a href="#">All Waiting Time</a></li>
                                <li><a href="#">Open Time</a></li>
                                <li><a href="#">Size of your Organization</a></li>
                            </ul>
                        </div>
                    </span>
                </div>

                <div className="sidebar-hover-content-box">
                    <h2>Ipsum</h2>
                    <div className="sidebar-hover-checkbox-collection">
                        <ul>
                            <li>
                                <a href="#" className="checkbox-button">
                                    <span className="checkbox"><i className="fa fa-check"/></span> <span className="chat-avatar" style={{backgroundImage: 'url(./img/avatar6.png)'}}/> <span className="name">Eduardo Hall</span>
                                </a>
                            </li>

                            <li>
                                <a href="#" className="checkbox-button">
                                    <span className="checkbox"><i className="fa fa-check"/></span> <span className="chat-avatar" style={{backgroundImage: 'url(./img/avatar5.png)'}}/> <span className="name">Max Cook</span>
                                </a>
                            </li>

                            <li>
                                <a href="#" className="checkbox-button">
                                    <span className="checkbox"><i className="fa fa-check"/></span> <span className="chat-avatar" style={{backgroundImage: 'url(./img/avatar4.jpg)'}}/> <span className="name">Kim Rice</span>
                                </a>
                            </li>

                            <li>
                                <a href="#" className="checkbox-button">
                                    <span className="checkbox"><i className="fa fa-check"/></span> <span className="chat-avatar" style={{backgroundImage: 'url(./img/avatar6.png)'}}/> <span className="name">Stacy Mason</span>
                                </a>
                            </li>

                            <li>
                                <a href="#" className="checkbox-button">
                                    <span className="checkbox"><i className="fa fa-check"/></span> <span className="chat-avatar" style={{backgroundImage: 'url(./img/avatar5.png)'}}/> <span className="name">Eduardo Hall</span>
                                </a>
                            </li>

                            <li>
                                <a href="#" className="checkbox-button">
                                    <span className="checkbox"><i className="fa fa-check"/></span> <span className="chat-avatar" style={{backgroundImage: 'url(./img/avatar6.png)'}}/> <span className="name">Max Cook</span>
                                </a>
                            </li>

                            <li>
                                <a href="#" className="checkbox-button">
                                    <span className="checkbox"><i className="fa fa-check"/></span> <span className="chat-avatar" style={{backgroundImage: 'url(./img/avatar5.png)'}}/> <span className="name">Kim Rice</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>


                <div className="sidebar-hover-content-box">
                    <a href="#" className="button">Update Filter</a>
                </div>

                <div className="sidebar-hover-content-box full-panel">
                    <h2>Filtering Options:</h2>
                    <div className="filter-options">
                        <ul>
                            <li><i className="fa fa-exclamation-circle"/> <span>Status:</span> Awaiting Agent</li>
                            <li><i className="fa fa-calendar-o"/> <span>Date:</span> Last Week</li>
                            <li><i className="fa fa-users"/> <span>Department:</span> Tech Support</li>
                        </ul>
                    </div>
                </div>
                <div className="sidebar-hover-content-box full-panel">
                    <h3>Editing a filter</h3>
                    <p>Print this page to PDF for the complete set of vectors. Or to use on the desktop, install FontAwesome.otf, set it as the font in your application, and copy and paste the icons directly from this page into your designs.</p>
                </div>

                <div className="sidebar-hover-footer">
                    <a href="#" className="button">Delete Filter</a>
                </div>
             </div>
        </div>
        );
    }
}
