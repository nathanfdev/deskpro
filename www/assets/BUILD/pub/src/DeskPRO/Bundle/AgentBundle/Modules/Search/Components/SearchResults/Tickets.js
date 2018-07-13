import React from 'react';
import PropTypes from 'prop-types';

export default class Tickets extends React.Component {
  static propTypes = {
    tickets: PropTypes.array
  };

  render() {
    const { tickets } = this.props;
    return (
      <section>
        <header><h1>Tickets</h1> <span className="count">({tickets.length})</span></header>
        <table>
          <tbody>
            {tickets.map(ticket =>
              <tr key={ticket.id}>
                <td>
                  <span className="id">{`#${ticket.id}`}</span>
                </td>
                <td className="title">
                  {ticket.subject}
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </section>
    );
  }
}
