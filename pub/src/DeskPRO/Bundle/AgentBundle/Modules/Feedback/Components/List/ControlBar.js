import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/FeedbackListActions'
import $ from "jquery";

@connect(state => state.FeedbackList)

export class ControlBar extends React.Component {

    constructor(props) {
        super(props);
    }

    showOrderChoice(event) {
        event.preventDefault();
        event.stopPropagation();
        var elem = $(event.target),
            filterChoice = elem.closest('a.ticket-control-button').find('div.focus-choice');
        $('div.dropdown-choice').hide();
        filterChoice.show();
    }

    showFilterChoice(event) {
        event.preventDefault();
        event.stopPropagation();
        var elem = $(event.target),
            filterChoice = elem.closest('a.ticket-control-button').find('div.filter-choice');
        $('div.dropdown-choice').hide();
        filterChoice.show();
    }

    orderChosen(event) {
        event.preventDefault();
        event.stopPropagation();
        const {sort} = this.props;
        var elem = $(event.target),
            name = elem.text();
        sort.sort = elem.data('field');
        this.setState({sortName: name});
        elem.closest('a.ticket-control-button').find('span.focus').text(name);
        $('div.dropdown-choice').hide();
        console.log('Set sort: ', sort);
    }

    filterChosen(event) {
        event.preventDefault();
        event.stopPropagation();
        var elem = $(event.target),
            target = elem.closest('a.ticket-control-button').find('span.down');
        target.text(elem.text()).append('<i class="fa fa-caret-down"/>');
        $('div.dropdown-choice').hide();
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

    changeView(event) {
        event.preventDefault();
        event.stopPropagation();
        const {dispatch, view, query, filters, sort} = this.props;
        $('div.dropdown-choice').hide();
        var elem = $(event.target);
        if (view === 'list') {
            elem.text('Table');
        }
        else {
            elem.text('List');
        }
        dispatch(actions.switchView());
        dispatch(actions.loadFeedbackList(query, filters, sort));
        console.log(this.props);
    }

    render() {
        const {view, sort, sortName} = this.props;
        return (
            <div className="tickets-control-bar">

                <div className="bulk-edit-control">
                    <a href="#">
                        <span className="checkbox">
                            <i className="fa fa-check"/>
                        </span>
                    </a>
            <span className="count" style={{display: "none"}}>
              <span>X</span>
            </span>
                </div>

                <span className="ticket-controls-default">
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
                </a>

                <a href="#" className="ticket-control-button">
                    <span className="title">View:</span>
                    <span className="focus" onClick={this.changeView.bind(this)}>{view}</span>
                </a>
            </span>
            </div>
        );
    }
}
