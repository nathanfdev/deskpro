import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { peopleSelector, articlesSelector }
  from '../../../../Selectors/list';
import { Table, Th, Td, TdId, PersonInTable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';

import { connect } from 'react-redux';
@connect(state => {
  return {
    people: peopleSelector(state),
    articles: articlesSelector(state)
  };
})

@injectIntl
export class ArticlesTableContainer extends Component {
  static propTypes = {
    intl: intlShape.isRequired,
    people: PropTypes.object.isRequired,
    articles: PropTypes.object.isRequired
  };

  render() {
    const {articles, people} = this.props;

    return (
      <Table>
        <thead>
        <tr>
          <Th sort="id" title="ID" visible/>
          <Th sort="author_name" title="Author" visible/>
          <Th sort="date_created" title="Created" visible/>
          <Th sort="date_updated" title="Updated" visible/>
          <Th sort="status" title="Status" visible/>
          <Th title="Labels" visible/>
          <Th sort="title" title="Title" visible/>
        </tr>
        </thead>
        <tbody>
        {articles.map((article, index) =>
            <tr key={index}>
              <TdId visible>
                {article.id}
              </TdId>
              <Td visible>
                <PersonInTable person={people.get(article.person)}/>
              </Td>
              <Td visible>
                <div className="dpw--timer"><FormattedRelative value={article.date_created}/></div>
              </Td>
              <Td visible>
                <div className="dpw--timer"><FormattedRelative value={article.date_updated}/></div>
              </Td>
              <Td visible>
                {article.status}
              </Td>
              <Td visible>
                @ToDo some labels stuff
              </Td>
              <Td className="item-title">
                <a href="#"><SlicedString string={article.title}/></a>
              </Td>
            </tr>
        )
        }
        </tbody>
      </Table>
    );
  }
}