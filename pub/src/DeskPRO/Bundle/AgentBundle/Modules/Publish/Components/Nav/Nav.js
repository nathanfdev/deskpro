import React from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionGroupedHeader, TabsPane, Tab, NestedList, ButtonsPane,
         Button, ListGroupingControl }
       from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

export class Nav extends React.Component {
  render() {
    const { lists, labels, grouping, onGroupingChange, toggleGroupingVisibility } = this.props;

    return (
      <NavFrame>
        <div part="outer">

          <ListGroupingControl
            title="Articles"
            options={grouping.options}
            visible={grouping.visibility.articles}
            onChange={onGroupingChange('articles')}
          />

          <ListGroupingControl
            title="News"
            options={grouping.options}
            visible={grouping.visibility.news}
            onChange={onGroupingChange('news')}
          />

          <ListGroupingControl
            title="Downloads"
            options={grouping.options}
            visible={grouping.visibility.downloads}
            onChange={onGroupingChange('downloads')}
          />

        </div>

        <div part="inner">
          <NavFrameHeader icon="fa-edit">Publish</NavFrameHeader>
          <TabsPane>

            <Tab title="KB">
              <SectionsPane>
                <Section>
                  <SectionGroupedHeader count={lists.articles.count} callback={toggleGroupingVisibility('articles')}>
                    Knowledgebase
                  </SectionGroupedHeader>

                  <NestedList items={lists.articles.nested} groups={labels.articles} />
                </Section>
              </SectionsPane>

              <ButtonsPane>
                <Button title="Glossary" icon="fa-quote-left" />
                <Button title="Search" icon="fa-search" />
                <Button title="Comments" icon="fa-comments-o" />
              </ButtonsPane>
            </Tab>

            <Tab title="News">
              <SectionsPane>
                <Section>
                  <SectionGroupedHeader count={lists.news.count} callback={toggleGroupingVisibility('news')}>
                    News
                  </SectionGroupedHeader>

                  <NestedList items={lists.news.nested} groups={labels.news} />
                </Section>
              </SectionsPane>
            </Tab>

            <Tab icon="fa-download">
              <SectionsPane>
                <Section>
                  <SectionGroupedHeader count={lists.downloads.count} callback={toggleGroupingVisibility('downloads')}>
                    Downloads
                  </SectionGroupedHeader>

                  <NestedList items={lists.downloads.nested} groups={labels.downloads} />
                </Section>
              </SectionsPane>
            </Tab>

            <Tab title="Todos"></Tab>

          </TabsPane>
        </div>
      </NavFrame>
    );
  }
}
