import React from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionGroupedHeader, TabsPane, Tab, NestedList, ButtonsPane,
         Button }
       from 'DeskPRO/Bundle/Agentbundle/Modules/Application/Components/NavFrame/index';

export class Nav extends React.Component {
  render() {
    const { articles, news, downloads, categories } = this.props;

    return (
      <NavFrame>
        <NavFrameHeader icon="fa-edit">Publish</NavFrameHeader>
        <TabsPane>

          <Tab title="KB">
            <SectionsPane>
              <Section>
                <SectionGroupedHeader count={articles.count} callback={()=>alert(1)}>
                  Knowledgebase
                </SectionGroupedHeader>

                <NestedList items={articles.nested} groups={categories.articles} />
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
                <SectionGroupedHeader count={news.count} callback={()=>alert(2)}>
                  News
                </SectionGroupedHeader>

                <NestedList items={news.nested} groups={categories.news} />
              </Section>
            </SectionsPane>
          </Tab>

          <Tab icon="fa-download">
            <SectionsPane>
              <Section>
                <SectionGroupedHeader count={downloads.count} callback={()=>alert(3)}>
                  Downloads
                </SectionGroupedHeader>

                <NestedList items={downloads.nested} groups={categories.downloads} />
              </Section>
            </SectionsPane>
          </Tab>

          <Tab title="Todos"></Tab>

        </TabsPane>
      </NavFrame>
    );
  }
}
