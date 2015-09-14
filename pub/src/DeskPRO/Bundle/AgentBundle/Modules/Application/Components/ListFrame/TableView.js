import React from 'react';

export class TableView extends React.Component {


  render() {
    return (
      <div className="dpmw--items-table-list">
        <table>
          {this.props.children}
        </table>
      </div>
    );
  }
}

export class TableBody extends React.Component {

  render() {

    return (
      <tbody>
      {this.props.children}
      </tbody>
    );
  }
}
export class TableHeader extends React.Component {

  render() {

    return (
      <thead>
      <tr>
        {this.props.children}
      </tr>
      </thead>
    );
  }

}

export class Td extends React.Component {

  render() {
    const { element,field } = this.props;

    return (
      <td className={field.className}>
        {this.renderTdContent(element, field)}
      </td>
    );
  }

  renderTdContent(element, field) {
    if (field.name === 'id') {
      return (
        <span className="dpw--item-id">#{element.id}</span>
      )
    }
    else if (field.name === 'author_name') {
      return (
        <div className="user">
          <span className="dpw--avatar-face" style={{backgroundImage: "url(../img/avatars/avatar1.png)"}}></span>
          <span className="agent-name">{element.author_name}</span>
        </div>
      )
    }
    else if (field.name === 'title') {
      return (
        <a href="#">{element.title}</a>
      )
    }
    else if (field.name === 'content') {
      return (
        element.content.substr(0,100)
      )
    }

    return (element[field.name]);
  }

}

