import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/FeedbackListActions'
import $ from "jquery";

@connect(state => state.FeedbackList)

export class OrderBy extends React.Component {

    showOrderChoice(event) {
        event.preventDefault();
        event.stopPropagation();
        var elem = $(event.target),
            filterChoice = elem.closest('a.ticket-control-button').find('div.focus-choice');
        $('div.dropdown-choice').hide();
        filterChoice.show();
    }

    orderChosen(event) {
        event.preventDefault();
        event.stopPropagation();
        const {dispatch, sort, filters, query} = this.props;
        var elem = $(event.target),
            name = elem.text();
        sort.sort = elem.data('field');
        this.setState({sortName: name});
        elem.closest('a.ticket-control-button').find('span.focus').text(name);
        $('div.dropdown-choice').hide();
        dispatch(actions.loadFeedbackList(sort, filters, query));
    }

    changeSortDirection(event) {
        event.preventDefault();
        event.stopPropagation();
        const {dispatch, sort, filters, query} = this.props;
        $('div.dropdown-choice').hide();
        var elem = $(event.target);
        if (elem.hasClass('asc')) {
            elem.removeClass('asc').text('Desc ');
            elem.siblings('i.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
            sort.order = 'desc';
        }
        else {
            elem.addClass('asc').text('Asc ');
            elem.siblings('i.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
            sort.order = 'asc';
        }
        dispatch(actions.loadFeedbackList(sort, filters, query));
    }

    render() {
        const {sort, sortName} = this.props;
        return (
            <a href="#" className="ticket-control-button">
                <span className="title">Order by:</span>
                <span className="focus" onClick={this.showOrderChoice.bind(this)}>{sortName}</span>
                <span className={sort.order} onClick={this.changeSortDirection.bind(this)}>Asc </span>
                <i className="fa fa-caret-down"/>

                <div className="focus-choice dropdown-choice">
                    <ul>
                        <li onClick={this.orderChosen.bind(this)} data-field="date_created">Date</li>
                        <li onClick={this.orderChosen.bind(this)} data-field="total_rating">Rating</li>
                        <li onClick={this.orderChosen.bind(this)} data-field="num_ratings">Number of votes</li>
                    </ul>
                </div>
            </a>
        );
    }
}