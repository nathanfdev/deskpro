import React from "react";

export default class TasksNavPeople extends React.Component {
    render() {
        return (<section className="tasks-nav-labels">
            <div className="list-sidebar-title">
                Labels <a href="#" className="title-down"><i className="fa fa-caret-down"/></a>
            </div>

            <ul>
                <li>
                    <div className="list-counter-bucket">
                        <a href="#" className="list-counter"
                           onclick="showFilterOptions(this); return false;">26</a>
                    </div>
                    <a href="#" className="item"><i className="fa fa-tag"/> Label</a>
                </li>
                <li>
                    <div className="list-counter-bucket">
                        <a href="#" className="list-counter"
                           onclick="showFilterOptions(this); return false;">26</a>
                    </div>
                    <a href="#" className="item"><i className="fa fa-tag"/> Label</a>
                </li>
                <li>
                    <div className="list-counter-bucket">
                        <a href="#" className="list-counter"
                           onclick="showFilterOptions(this); return false;">26</a>
                    </div>
                    <a href="#" className="item"><i className="fa fa-tag"/> Label</a>
                </li>
            </ul>
                </section>);
    }
}