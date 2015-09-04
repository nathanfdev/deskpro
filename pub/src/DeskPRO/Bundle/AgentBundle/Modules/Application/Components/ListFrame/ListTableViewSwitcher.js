/**
 * Component to toggle view between the two modes: List and Table
 */
import React from 'react';
import * as actions from '../../Actions/ControlBarActions'
import $ from "jquery";

export class ListTableViewSwitcher extends React.Component {
    changeView(event) {
        event.preventDefault();
        event.stopPropagation();
        const {dispatch} = this.props;
        $('div.dropdown-choice').hide();
        dispatch(actions.switchViewMode());
    }

    showViewModeChoice(event) {
        event.preventDefault();
        event.stopPropagation();
        var elem = $(event.target),
            viewModeChoice = elem.closest('a.ticket-control-button').find('div.view-mode-choice');
        $('div.dropdown-choice').hide();
        viewModeChoice.show();
    }


    render() {
        return (
            <a href="#" className="ticket-control-button">
                <span className="title">View:</span>
                <span className="focus" onClick={this.showViewModeChoice.bind(this)}>{this.props.viewMode}</span>
                {this.props.children}
            </a>
        );
    }
}