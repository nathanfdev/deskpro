import React from 'react';
import PropTypes from 'prop-types';
import { Icon } from '@deskpro/react-components';

export default class Articles extends React.Component {
  static propTypes = {
    articles: PropTypes.array
  };

  render() {
    const { articles } = this.props;
    return (
      <section>
        <table className="articles">
          <tbody>
            {articles.map(article =>
              <tr key={article.id}>
                <td>
                  <span className="id">{`#${article.id}`}</span>
                  <span className="title">{article.title}</span>< br />
                  <span className="status">{article.status}</span> <span className="content">{article.content}</span>
                </td>
                <td className="actions">
                  <Icon name="file-text-o" /><br />
                  <Icon name="link" />
                </td>
              </tr>
          )}
          </tbody>
        </table>
      </section>
    );
  }
}
