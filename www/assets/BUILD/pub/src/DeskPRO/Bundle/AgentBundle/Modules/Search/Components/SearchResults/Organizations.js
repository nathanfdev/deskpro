import React from 'react';
import PropTypes from 'prop-types';

export default class Organizations extends React.Component {
  static propTypes = {
    organizations: PropTypes.array
  };

  render() {
    const { organizations } = this.props;
    return (
      <section>
        <header><h1>Organizations</h1></header>
        <table>
          <tbody>
            {organizations.map(org =>
              <tr key={org.id}>
                <td className="title">
                  {org.name}
                </td>
                <td>
                  {`${org.members} Members`}
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </section>
    );
  }
}
