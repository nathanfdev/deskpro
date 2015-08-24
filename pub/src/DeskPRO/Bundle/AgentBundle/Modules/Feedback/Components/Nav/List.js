import React, { Component, PropTypes } from 'react';
import { ListFrame }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

export class List extends React.Component {

    render() {
        const { feedback } = this.props;
        return (
            <ListFrame>
                <div>FeedbackList</div>
            </ListFrame>
        )
    }
}