import React from 'react';
import { SectionsPane, Section, SectionHeader }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { ListFrame }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackCard} from './FeedbackCard';
import { ControlBar} from './ControlBar';
import { TableView} from './TableView';


export class ListContainer extends React.Component {

    render() {
        const { feedback, view } = this.props;
        let itemKey = 0;

        return (
            <ListFrame>
                <SectionsPane>
                    <Section>
                        <ControlBar {...this.props}/>
                    </Section>
                    <Section>
                        {view === 'list' ?
                            feedback.map(item =>
                                    <FeedbackCard key={itemKey++} feedback={item}/>
                            ) : <TableView feedback={feedback}/>}
                    </Section>
                </SectionsPane>
            </ListFrame>
        );
    }
}
