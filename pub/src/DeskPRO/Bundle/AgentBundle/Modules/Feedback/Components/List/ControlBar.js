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

    focusChosen(event) {
        event.preventDefault();
        event.stopPropagation();
        var elem = $(event.target);
        elem.closest('a.ticket-control-button').find('span.focus').text(elem.text());
        $('div.dropdown-choice').hide();
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
        $('div.dropdown-choice').hide();
        var elem = $(event.target);
        if (elem.hasClass('asc')) {
            elem.removeClass('asc').text('Desc ');
            elem.siblings('i.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
        }
        else {
            elem.addClass('asc').text('Asc ');
            elem.siblings('i.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
        }
    }

    changeView(event) {
        console.log('ChangeView: ', this.props);
        const {filters, dispatch, query} = this.props;
        event.preventDefault();
        event.stopPropagation();
        $('div.dropdown-choice').hide();
        var elem = $(event.target);
        if (filters.view === 'list') {
            elem.text('Table');
            filters.view = 'table';
        }
        else {
            elem.text('List');
            filters.view = 'list';
        }
        dispatch(actions.loadFeedbackList(query));
    }

    render() {
        const {feedback, filters} = this.props;
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
                <span className="focus" onClick={this.showOrderChoice.bind(this)}>{filters.sort}</span>
                <span className={filters.order} onClick={this.changeSortDirection.bind(this)}>Asc </span>
                <i className="fa fa-caret-down"/>

                <div className="focus-choice dropdown-choice">
                    <ul>
                        <li onClick={this.focusChosen.bind(this)}>Date</li>
                        <li onClick={this.focusChosen.bind(this)}>Rating</li>
                        <li onClick={this.focusChosen.bind(this)}>Number of votes</li>
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
                    <span className="focus" onClick={this.changeView.bind(this)}>{filters.view}</span>
                </a>
            </span>
            </div>
        );
    }
}
