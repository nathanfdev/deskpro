import React from 'react';
import { connect } from 'redux/react';
import $ from "jquery";

@connect(state => state.FeedbackList)

export class ControlBar extends React.Component {

    constructor(props) {
        super(props);
    }

    showFocusChoice(event) {
        event.preventDefault();
        event.stopPropagation();
        $(event.target).closest('a').find('div.filter-choice').show();
    }

    showDownChoice(event) {
        event.preventDefault();
        event.stopPropagation();
        var elem = $(event.target);
        console.log('Down: ', elem.siblings('div.down-choice'));
        elem.siblings('div.down-choice').show();
    }

    focusChosen(event) {
        event.preventDefault();
        event.stopPropagation();
        var elem = $(event.target);
        elem.closest('a.ticket-control-button').find('span.focus').text(elem.text());
        elem.closest('div.filter-choice').hide();
    }

    downChosen(event) {
        event.preventDefault();
        event.stopPropagation();
        var elem = $(event.target);
        elem.closest('a.ticket-control-button').find('span.down').text(elem.text());
        elem.closest('div.down-choice').hide();
    }

    render() {
        const {feedback} = this.props;
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
                <span className="focus" onClick={this.showFocusChoice.bind(this)}>Date</span>
                <span className="down" onClick={this.showDownChoice.bind(this)}>Asc <i className="fa fa-caret-down"/></span>

                <div className="focus-choice"
                     style={{display:'none', position:'fixed', top:0, left:0, zIndex:1000}}>
                    <ul>
                        <li onClick={this.focusChosen.bind(this)}>Date</li>
                        <li onClick={this.focusChosen.bind(this)}>Rating</li>
                        <li onClick={this.focusChosen.bind(this)}>Number of votes</li>
                    </ul>
                </div>

                <div className="down-choice"
                     style={{display:'none', position:'fixed', zIndex:1000}}>
                    <ul>
                        <li onClick={this.downChosen.bind(this)}>Asc</li>
                        <li onClick={this.downChosen.bind(this)}>Desc</li>
                    </ul>
                </div>
            </a>

            <a href="#" className="ticket-control-button">
                <span className="title">Filter by:</span>
                <span className="focus">12</span>
                <span className="down">Completed <i className="fa fa-caret-down"/></span>
            </a>

            <a href="#" className="ticket-control-button">
                <span className="title">View:</span>
              <span className="multi">
                List
                <span className="multi-down"><i className="fa fa-caret-down"/></span>
              </span>
            </a>
          </span>
            </div>
        );
    }
}
