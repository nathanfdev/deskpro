import React, { Component, PropTypes } from 'react';
import { SectionsPane, Section, SectionHeader }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { ListFrame }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackCard} from './FeedbackCard';
import { ControlBar} from './ControlBar';
import { TableView} from './TableView';

export class List extends React.Component {

    render() {
        const { feedback } = this.props;

        let itemKey = 0;
        let view = 'table';

        return (
            <ListFrame>
                <SectionsPane>
                    <Section>
                        <ControlBar/>
                    </Section>
                    <Section>
                        {view === 'cards' ?
                            feedback.map(item =>
                                    <FeedbackCard key={itemKey++} feedback={item}/>
                            ) : <TableView/>}
                    </Section>
                </SectionsPane>
            </ListFrame>
        )
    }
}