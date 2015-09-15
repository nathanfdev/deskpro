import React from 'react';
import { connect } from 'redux/react';
import AgentsListItem from './AgentsListItem.js'
import * as actions from '../Actions/imListActions.js';

export default class AgentsList extends React.Component {
    render() {
        return (
            <ul className="im-list">
                <AgentsListItem  />
            </ul>
        );
    }
}