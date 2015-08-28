import React, { Component, PropTypes } from 'react';
import { Section, SectionHeader, ListItem }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

export class Pending extends React.Component {

    render() {
        const { toValidateCount, commentsToReviewCount, onClick } = this.props;

        return (
            <Section>
                <SectionHeader>Pending</SectionHeader>
                <ul>
                    <div onClick={onClick.bind(this, {'awaiting_validation':1})}>
                        <ListItem count={toValidateCount} label="Feedback to Validate"/>
                    </div>
                    <ListItem count={commentsToReviewCount} label="Comments to Review"/>
                </ul>
            </Section>
        );
    }
}