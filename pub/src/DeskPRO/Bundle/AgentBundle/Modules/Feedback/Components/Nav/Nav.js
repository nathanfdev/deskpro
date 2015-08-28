import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, TabsPane, Tab, ListItem, LabelsDictionary, NestedList }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { Pending } from './Pending';
import { StatusTab } from './StatusTab';
import { TypeTab } from './TypeTab';
import { CategoryTab } from './CategoryTab';

export class Nav extends Component {

    render() {
        const { labels, types, toValidateCount, commentsToReviewCount, statuses, customCategories, onClick } = this.props;

        return (
            <NavFrame>
                <NavFrameHeader icon="fa-thumbs-up">Feedback</NavFrameHeader>

                <SectionsPane>

                    <Pending toValidateCount={toValidateCount} commentsToReviewCount={commentsToReviewCount}
                             onClick={onClick.bind(this)}/>

                    <Section>
                        <TabsPane>
                            <Tab title="Status">
                                <StatusTab statuses={statuses} onClick={onClick.bind(this)}/>
                            </Tab>

                            <Tab title="Labels">
                                <LabelsDictionary labels={labels} onClick={onClick.bind(this)}/>
                            </Tab>
                        </TabsPane>

                        <TabsPane>
                            <Tab title="Type">
                                <TypeTab types={types} onClick={onClick.bind(this)}/>
                            </Tab>
                            <Tab title="Categories">
                                <CategoryTab customCategories={customCategories} onClick={onClick.bind(this)}/>
                            </Tab>
                        </TabsPane>

                    </Section>
                </SectionsPane>
            </NavFrame>
        );
    }
}