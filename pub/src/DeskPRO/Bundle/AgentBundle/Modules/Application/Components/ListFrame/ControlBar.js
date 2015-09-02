import React from 'react';
import { connect } from 'redux/react';

export class ControlBar extends React.Component {
    render() {
        return (
            <div className="tickets-control-bar">

                <div className="bulk-edit-control">
                    <a href="#">
                        <span className="checkbox">
                            <i className="fa fa-check"/>
                        </span>
                    </a>
                    <span className="count" style={{display: "none"}}><span>X</span></span>
                </div>

                <span className="ticket-controls-default">
                    {this.props.children}
                    </span>
            </div>
        );
    }
}


