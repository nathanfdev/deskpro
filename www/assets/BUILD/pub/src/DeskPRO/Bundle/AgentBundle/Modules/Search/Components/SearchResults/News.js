import React from 'react';
import PropTypes from 'prop-types';

export default class News extends React.Component {
  static propTypes = {
    news: PropTypes.array
  };

  render() {
    const { news } = this.props;
    return (
      <section>
        <table>
          <tbody>
            {news.map(n =>
              <tr key={n.id}>
                <td>
                  <span className="id">{`#${n.id}`}</span>
                  <span className="title">{n.title}</span>< br />
                  {n.status}
                </td>
              </tr>
          )}
          </tbody>
        </table>
      </section>
    );
  }
}
