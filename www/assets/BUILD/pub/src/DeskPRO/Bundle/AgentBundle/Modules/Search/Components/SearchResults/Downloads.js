import React from 'react';
import PropTypes from 'prop-types';

export default class Downloads extends React.Component {
  static propTypes = {
    downloads: PropTypes.array
  };

  render() {
    const { downloads } = this.props;
    return (
      <section>
        <table>
          <tbody>
            {downloads.map(download =>
              <tr key={download.id}>
                <td>
                  <span className="id">{`#${download.id}`}</span>
                </td>
                <td>
                  <span className="title">{download.title}</span>< br />
                  {download.status}
                </td>
              </tr>
          )}
          </tbody>
        </table>
      </section>
    );
  }
}
