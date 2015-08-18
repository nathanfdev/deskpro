import React from "react";

import * as FeedbackActions from "../Actions/FeedbackListActions";

var ListItemWrapper = React.createClass({
    render: function () {
        return (
            <li>
                <div className="list-counter-bucket">
                    <a className="list-counter" href="#"
                       onclick="showFilterOptions(this); return false;">{this.props.data.value}</a>
                </div>
                <a href="#" className="item"
                   onmouseover="toggleCountBucket(this);">{this.props.data.title}</a>;
            </li>
        )
    }
});

export default class FeedbackNavTypes extends React.Component {
    render() {
        return (
            <section className="sidebar-list feedback-nav-groups">
                <div className="list-sidebar-title">Type</div>
                <ul>
                    {this.props.types.map(function (result) {
                        return <ListItemWrapper key={result.title} data={result}/>;
                    })}
                </ul>
            </section>
        );
    }
}
