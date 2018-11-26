import React from 'react';
import PropTypes from 'prop-types';

export default class Feedback extends React.Component {
  static propTypes = {
    feedback: PropTypes.array
  };

  render() {
    const { feedback } = this.props;
    return (
      <section>
        <table>
          <tbody>
            {feedback.map(f =>
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
