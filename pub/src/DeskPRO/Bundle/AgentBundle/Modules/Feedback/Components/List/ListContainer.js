import React from 'react';
import { SectionsPane, Section, SectionHeader }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { ListFrame, ControlBar, ListTableViewSwitcher }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackCard} from './FeedbackCard';
import { TableView} from './TableView';
import { OrderBy} from './OrderBy';
import { FilterBy} from './FilterBy';
import { connect } from 'redux/react';

@connect(state => state.control_bar)

export class ListContainer extends React.Component {

    render() {
        const { feedback, viewMode, sortTable, sort, sortName, filters, query } = this.props;
        let itemKey = 0;

        return (
            <ListFrame>
                <SectionsPane>
                    <Section>
                        <ControlBar {...this.props}>
                            <OrderBy sort={sort} sortName={sortName}/>
                            <FilterBy filters={filters} query={query}/>
                            <ListTableViewSwitcher {...this.props}/>
                        </ControlBar>
                    </Section>
                    <Section>
                        {viewMode === 'list' ?
                            feedback.map(item =>
                                    <FeedbackCard key={itemKey++} feedback={item}/>
                            ) : <TableView feedback={feedback} sortTable={sortTable.bind(this)}/>}
                    </Section>
                </SectionsPane>
            </ListFrame>
        );
    }
}
