import React from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, SectionGroupedHeader, TabsPane, Tab,
  NestedList, ListItem, ButtonsPane, Button, ListGroupingControl }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class Nav extends React.Component {

  render() {
    const { lists, labels, grouping, onGroupingChange, toggleGroupingVisibility, setMine, onClick, dispatch, dpWindow } = this.props;
    const currentApp = dpWindow.get('activeAppId');
    const mine = lists.get('todo').get('articles').mine;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <div part="outer">

          <ListGroupingControl
            title="Articles"
            options={grouping.get('options')}
            visible={grouping.get('visibility').get('articles')}
            onChange={onGroupingChange('articles')}
            />

          <ListGroupingControl
            title="News"
            options={grouping.get('options')}
            visible={grouping.get('visibility').get('news')}
            onChange={onGroupingChange('news')}
            />

          <ListGroupingControl
            title="Downloads"
            options={grouping.get('options')}
            visible={grouping.get('visibility').get('downloads')}
            onChange={onGroupingChange('downloads')}
            />

        </div>

        <div part="inner">
          <NavFrameHeader icon="icon-dp-streamline-edit-1" currentApp={currentApp}>
            Publish
          </NavFrameHeader>
          <TabsPane>

            <Tab title="KB">
              <SectionsPane>
                <Section>
                  <SectionGroupedHeader count={lists.get('articles').count}
                                        callback={toggleGroupingVisibility('articles')}>
                    Knowledgebase
                  </SectionGroupedHeader>

                  <NestedList items={lists.get('articles').nested} groups={labels.articles} onClick={onClick.articles}/>
                </Section>
              </SectionsPane>

              <ButtonsPane>
                <Button title="Glossary" icon="fa-quote-left"/>
                <Button title="Search" icon="fa-search"/>
                <Button title="Comments" icon="fa-comments-o"/>
              </ButtonsPane>
            </Tab>

            <Tab title="News">
              <SectionsPane>
                <Section>
                  <SectionGroupedHeader count={lists.get('news').count} callback={toggleGroupingVisibility('news')}>
                    News
                  </SectionGroupedHeader>

                  <NestedList items={lists.get('news').nested} groups={labels.news} onClick={onClick.news}/>
                </Section>
              </SectionsPane>
            </Tab>

            <Tab icon="fa-download">
              <SectionsPane>
                <Section>
                  <SectionGroupedHeader count={lists.get('downloads').count}
                                        callback={toggleGroupingVisibility('downloads')}>
                    Downloads
                  </SectionGroupedHeader>

                  <NestedList items={lists.get('downloads').nested} groups={labels.downloads}
                              onClick={onClick.downloads}/>
                </Section>
              </SectionsPane>
            </Tab>

            <Tab title="Todos">
              <SectionsPane>
                <Section>
                  <SectionHeader>
                    Articles
                    <div className="sla" style={{display: 'inline-block', float: 'right'}}>
                      <span className={mine ? 'selected' : ''} onClick={setMine(true)}>Mine</span>
                      <span className={!mine ? 'selected' : ''} onClick={setMine(false)}>All</span>
                    </div>
                  </SectionHeader>

                  <ul>
                    <ListItem label="Draft Articles" count={lists.get('todo').get('articles').draft} onClick={onClick.draftArticles}/>
                    <ListItem label="Pending Articles" count={lists.get('todo').get('articles').pending}
                              onClick={onClick.pendingArticles}/>
                  </ul>
                </Section>
                <Section>
                  <SectionHeader>Comments</SectionHeader>

                  <ul>
                    <ListItem label="Comments to validate"
                              count={lists.get('todo').get('comments').get('validate').count}
                              onClick={onClick.allCommentsToValidate}>
                      <NestedList
                        depth="2"
                        items={lists.get('todo').get('comments').get('validate').get('nested')}
                        groups={labels.commentsToValidate}
                        onClick={onClick.commentsToValidate}
                        />
                    </ListItem>
                    <ListItem label="Comments to review" count={lists.get('todo').get('comments').get('review')}
                              onClick={onClick.commentsToReview}/>
                  </ul>
                </Section>
                <Section>
                  <SectionHeader>Translations</SectionHeader>
                  &nbsp;
                </Section>
              </SectionsPane>
            </Tab>

          </TabsPane>
        </div>
      </NavFrame>
    );
  }
}
