import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/FeedbackListActions'
import { List } from './List';
import $ from "jquery";

@connect(state => state.FeedbackList)

export class ListContainer extends React.Component {

    constructor(props) {
        super(props);
    }

    render() {
        const {feedback} = this.props;
        return (
            <List feedback={feedback} {...this.props} />
        );
    }
}
