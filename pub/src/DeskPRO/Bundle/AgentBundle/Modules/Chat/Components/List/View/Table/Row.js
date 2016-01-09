import React, {Component, PropTypes} from 'react';

export class Row extends React.Component {
  static propTypes = {
    element: PropTypes.object.isRequired
  };

  render() {
    const {element} = this.props;

    return (
      <tr>
        <td className="id-col"><span className="dpw--item-id">#{element.get('id')}</span></td>
        <td></td>
        <td className="agent-col">
          <div className="agent">
            <span className="dpw--avatar-face" style={{backgroundImage: 'url(../img/avatars/avatar4.png)'}}></span>
            {element.get('agent')}
          </div>
        </td>
        <td></td>
        <td className="item-title">{element.get('subject')}</td>
        <td></td>
        <td></td>
      </tr>);
  }
}