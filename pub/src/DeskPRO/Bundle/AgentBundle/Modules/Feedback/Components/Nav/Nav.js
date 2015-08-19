import React from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, TabsPane, Tab, ListItem, LabelsDictionary }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

export class Nav extends React.Component {
    render() {
        const { labels, types, toValidateCount, commentsToReviewCount, statuses } = this.props;
        let itemKey = 0;

        return (
            <NavFrame>
                <NavFrameHeader icon="fa-thumbs-up">Feedback</NavFrameHeader>
                <SectionsPane>
                    <Section>
                        <SectionHeader>Pending</SectionHeader>
                        <ul>
                            <ListItem count={toValidateCount} label="Feedback to Validate"/>
                            <ListItem count={commentsToReviewCount} label="Comments to Review"/>
                        </ul>
                    </Section>

                    <Section>
                        <TabsPane>
                            <Tab title="Status">
                                <ul>
                                    <ListItem count={statuses.new} label="New"/>
                                    <ListItem count={statuses.active.total} label="Active"/>
                                    <ul>
                                        {statuses.active.statuses.map(item =>
                                            <ListItem key={itemKey++} count={item.count} label={item.group}/>)}
                                    </ul>
                                    <ListItem count={statuses.closed.total} label="Closed"/>
                                    <ul>
                                        {statuses.closed.statuses.map(item =>
                                            <ListItem key={itemKey++} count={item.count} label={item.group}/>)}
                                    </ul>
                                    <ListItem count={statuses.hidden.total} label="Hidden"/>
                                    <ul>
                                        {statuses.hidden.statuses.map(item =>
                                            <ListItem key={itemKey++} count={item.count} label={item.group}/>)}
                                    </ul>
                                </ul>
                            </Tab>
                            <Tab title="Type">
                                <ul>
                                    {types.map(item =>
                                        <ListItem key={itemKey++} count={item.value} label={item.title}/>)}
                                </ul>
                            </Tab>
                            <Tab title="Labels">
                                <LabelsDictionary labels={labels}/>
                            </Tab>
                        </TabsPane>
                    </Section>
                </SectionsPane>
            </NavFrame>
        );
    }
}
