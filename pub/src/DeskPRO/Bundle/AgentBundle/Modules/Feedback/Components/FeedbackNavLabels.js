import React from "react";

import * as FeedbackActions from "../Actions/FeedbackListActions";

var ListItemWrapper = React.createClass({
    render: function () {
        return (
            <li>
                <div className="list-counter-bucket">
                    <a className="list-counter" href="#"
                       onclick="showFilterOptions(this); return false;">{this.props.data.cnt}</a>
                </div>
                <a href="#" className="item"
                   onmouseover="toggleCountBucket(this);">{this.props.data.label}</a>;
            </li>
        )
    }
});

export default class FeedbackNavLabels extends React.Component {
    render() {
        return (
            <section className="sidebar-list feedback-nav-groups">
                <div className="list-sidebar-title">Labels</div>
                <ul>
                    {this.props.labels.map(function (result) {
                        return <ListItemWrapper key={result.label} data={result}/>;
                    })}
                </ul>
            </section>
        );
    }
}
