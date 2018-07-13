import React from 'react';
import PropTypes from 'prop-types';

export default class People extends React.Component {
  static propTypes = {
    people: PropTypes.array
  };

  render() {
    const { people } = this.props;
    return (
      <section>
        <header><h1>People</h1></header>
        <table>
          <tbody>
            {people.map(person =>
              <tr key={person.id}>
                <td className="title">
                  {person.name}
                </td>
                <td>
                  {person.email}
                </td>
                <td>
                  {person.tickets}
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </section>
    );
  }
}
