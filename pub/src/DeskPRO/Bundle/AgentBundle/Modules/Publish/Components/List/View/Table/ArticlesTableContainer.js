import React, {Component, PropTypes} from 'react';
import { Table, Th, Td, TdId } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';

import { connect } from 'react-redux';
@connect(state => {
  return {
    articles: state.Publish.list.get('articles')
  };
})

export class ArticlesTableContainer extends Component {
  static propTypes = {
    articles: PropTypes.object.isRequired
  };

  render() {
    const {articles} = this.props;
    return (
      <Table>
        <thead>
        <tr>
          <Th sort="id" title="ID" visible/>
          <Th sort="title" title="Title" visible/>
          <Th sort="content" title="Content" visible/>
        </tr>
        </thead>
        <tbody>
        {articles.map((article, index) =>
            <tr key={index}>
              <TdId visible>
                {article.id}
              </TdId>
              <Td className="item-title" visible>
                <a href="#"><SlicedString string={article.title}/></a>
              </Td>
              <Td className="item-title" visible>
                <a href="#"><SlicedString string={article.content}/></a>
              </Td>
            </tr>
        )
        }
        </tbody>
      </Table>
    );
  }
}