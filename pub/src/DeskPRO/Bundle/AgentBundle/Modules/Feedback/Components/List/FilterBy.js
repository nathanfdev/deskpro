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
        const {query} = this.props;
        return (
            <a href="#" className="ticket-control-button">
                <span className="title">Filter by:</span>
                <span className="focus">12</span>
                    <span className="down" onClick={this.showFilterChoice.bind(this)}>
                        {query.hasOwnProperty('status') ? 'Type' : 'Status'} <i className="fa fa-caret-down"/>
                    </span>

                <div className="filter-choice dropdown-choice">
                    <ul>
                        {query.hasOwnProperty('status') ? '' : <li onClick={this.filterChosen.bind(this)}>Status</li>}
                        {query.hasOwnProperty('category') ? '' : <li onClick={this.filterChosen.bind(this)}>Type</li>}
                        {query.hasOwnProperty('custom_category') ? '' : <li onClick={this.filterChosen.bind(this)}>Category</li>}
                    </ul>
                </div>
            </a>);
    }
}