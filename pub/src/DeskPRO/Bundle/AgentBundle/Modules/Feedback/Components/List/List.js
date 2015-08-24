import React, { Component, PropTypes } from 'react';
import { SectionsPane, Section, SectionHeader }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { ListFrame }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackCard} from './FeedbackCard'

export class List extends React.Component {

    render() {
        const { feedback } = this.props;
        console.log(feedback);

        let itemKey = 0;

        return (
            <ListFrame>
                <SectionsPane>
                    <Section>
                        <SectionHeader>Feedback List</SectionHeader>
                        {feedback.map(item =>
                                <FeedbackCard key={itemKey++} feedback={item}/>
                        )}
                    </Section>
                </SectionsPane>
            </ListFrame>
        )
    }
}