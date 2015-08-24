import React from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, TabsPane, Tab, NestedList, ButtonsPane,
         Button }
       from 'DeskPRO/Bundle/Agentbundle/Modules/Application/Components/NavFrame/index';

export class Nav extends React.Component {
  render() {
    const { articles, categories } = this.props;

    return (
      <NavFrame>
        <NavFrameHeader icon="fa-edit">Publish</NavFrameHeader>
        <TabsPane>
          <Tab title="KB">
            <SectionsPane>
              <Section>
                <SectionHeader>
                  Knowledgebase
                  <div className="list-counter-bucket">
                    <a className="list-counter-dropdown active" href="#">
                      <span>&nbsp;</span>
                      <i className="fa fa-angle-down"></i>
                    </a>
                    <a className="list-counter active" href="#">{articles.total}</a>
                  </div>
                </SectionHeader>

                <NestedList items={articles.nested} groups={categories.article} />
              </Section>
            </SectionsPane>

            <ButtonsPane>
              <Button title="Glossary" icon="fa-quote-left" />
              <Button title="Search" icon="fa-search" />
              <Button title="Comments" icon="fa-comments-o" />
            </ButtonsPane>
          </Tab>
          <Tab title="News"></Tab>
          <Tab icon="fa-download"></Tab>
          <Tab title="Todos"></Tab>
        </TabsPane>
      </NavFrame>
    );
  }
}
