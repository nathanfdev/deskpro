import React, { Component, PropTypes } from 'react';
import { SectionsPane, Section, SectionHeader }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { ListFrame }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackCard} from './FeedbackCard';
import { ControlBar} from './ControlBar';
import { TableView} from './TableView';
import { connect } from 'redux/react';

@connect(state => state.FeedbackList)

export class List extends React.Component {

    render() {
        const { feedback, filters } = this.props;
        let itemKey = 0;

        return (
            <ListFrame>
                <SectionsPane>
                    <Section>
                        <ControlBar {...this.props}/>
                    </Section>
                    <Section>
                        {filters.view === 'list' ?
                            feedback.map(item =>
                                    <FeedbackCard key={itemKey++} feedback={item}/>
                            ) : <TableView/>}
                    </Section>
                </SectionsPane>
            </ListFrame>
        )
    }
}