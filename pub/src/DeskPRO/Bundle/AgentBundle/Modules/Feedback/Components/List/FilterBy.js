import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/FeedbackListActions'
import $ from "jquery";

@connect(state => state.FeedbackList)

export class FilterBy extends React.Component {

    showFilterChoice(event) {
        event.preventDefault();
        event.stopPropagation();
        var elem = $(event.target),
            filterChoice = elem.closest('a.ticket-control-button').find('div.filter-choice');
        $('div.dropdown-choice').hide();
        filterChoice.show();
    }


    filterChosen(event) {
        event.preventDefault();
        event.stopPropagation();
        var elem = $(event.target),
            target = elem.closest('a.ticket-control-button').find('span.down');
        target.text(elem.text()).append('<i class="fa fa-caret-down"/>');
        $('div.dropdown-choice').hide();
    }

    render() {
        return (
            <a href="#" className="ticket-control-button">
                <span className="title">Filter by:</span>
                <span className="focus">12</span>
                    <span className="down" onClick={this.showFilterChoice.bind(this)}>
                        Status <i className="fa fa-caret-down"/>
                    </span>

                <div className="filter-choice dropdown-choice">
                    <ul>
                        <li onClick={this.filterChosen.bind(this)}>Status</li>
                        <li onClick={this.filterChosen.bind(this)}>Type</li>
                        <li onClick={this.filterChosen.bind(this)}>Category</li>
                    </ul>
                </div>
            </a>);
    }
}