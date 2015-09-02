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

    render() {
        return (
            <a href="#" className="ticket-control-button">
                <span className="title">View:</span>
                <span className="focus" onClick={this.changeView.bind(this)}>{this.props.viewMode}</span>
            </a>
        );
    }
}