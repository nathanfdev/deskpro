import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/FeedbackListActions'
import $ from "jquery";
import { OrderBy } from './OrderBy';
import { View } from './View';
import { FilterBy } from './FilterBy';

@connect(state => state.FeedbackList)

export class ControlBar extends React.Component {

    constructor(props) {
        super(props);
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
                    <span className="count" style={{display: "none"}}><span>X</span></span>
                </div>

                <span className="ticket-controls-default">
                    <OrderBy sort={sort} sortName={sortName}/>
                    <FilterBy/>
                    <View view={view}/>
                </span>
            </div>
        );
    }
}
