import React from 'react';
import PropTypes from 'prop-types';

export default class CommunityTopic extends React.Component {
  static propTypes = {
    topics: PropTypes.array
  };

  render() {
    const { topics } = this.props;
    return (
      <section>
        <table>
          <tbody>
            {topics.map(f =>
              <tr key={f.id}>
                <td>
                  <span className="id">{`#${f.id}`}</span>
                  <span className="title">{f.title}</span>< br />
                  {f.status}
                </td>
              </tr>
          )}
          </tbody>
        </table>
      </section>
    );
  }
}
