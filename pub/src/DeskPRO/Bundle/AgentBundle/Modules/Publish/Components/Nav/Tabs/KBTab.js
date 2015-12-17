import React, {Component, PropTypes} from 'react';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { SectionsPane, Section, SectionGroupedHeader, NestedList, ButtonsPane, Button }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class KBTab extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    articles: PropTypes.object.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { loaded, articles, toggleGroupingVisibility, onClick} = this.props;
    return (
      <LoadIndicator loaded={loaded}>
        <SectionsPane>
          <Section ref="kb">
            <SectionGroupedHeader label="Knowledgebase"
                                  count={articles.get('count')}
                                  callback={toggleGroupingVisibility('articles')}/>
            <NestedList items={articles.get('nested').toJS()}
                        onClick={onClick}/>
          </Section>
        </SectionsPane>

        <ButtonsPane>
          <Button title="Glossary" icon="fa-quote-left"/>
          <Button title="Search" icon="fa-search"/>
          <Button title="Comments" icon="fa-comments-o"/>
        </ButtonsPane>
      </LoadIndicator>
    );
  }
}