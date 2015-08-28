import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/FeedbackListActions'
import $ from "jquery";

@connect(state => state.FeedbackList)

export class View extends React.Component {

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
        dispatch(actions.loadFeedbackList(query, sort));
    }

    render() {
        const {view} = this.props;
        return (
            <a href="#" className="ticket-control-button">
                <span className="title">View:</span>
                <span className="focus" onClick={this.changeView.bind(this)}>{view}</span>
            </a>
        );}
    }