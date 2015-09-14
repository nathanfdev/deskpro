import React from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import $ from "jquery";

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

export class Th extends React.Component {

  render() {
    const { field, sortTable } = this.props;
    return (
      <th className={'sortable '+ field.className}
          onClick={this.handleClick.bind(this, field.name, sortTable)}>
        {field.label}
      </th>
    );
  }

  handleClick(param, sortTable, event) {
    event.preventDefault();
    event.stopPropagation();
    let elem = $(event.target),
      siblings = elem.siblings('th'),
      caret = elem.find('i.fa');
    let order = elem.data('order') === constants.ORDER_DESC ? constants.ORDER_ASC : constants.ORDER_DESC;
    siblings.find('span.sort-direction').remove();
    siblings.data('order', '');
    elem.data('order', order);
    if (caret.length > 0) {
      caret.toggleClass('fa-caret-down').toggleClass('fa-caret-up');
    }
    else {
      elem.append('<span class="sort-direction"><i class="fa fa-caret-down"></i></span>')
    }
    sortTable(param, order);
  }

}

export class Row extends React.Component {

  render() {
    const { element, tableViewFields } = this.props;
    let filteredFields = tableViewFields.filter(function (field) {
      return field.status !== constants.FIELD_HIDDEN
    });
    filteredFields.sort(function (a, b) {
      return a.priority - b.priority
    });

    return (
      <tr className="single-row">
        {filteredFields.map((field, index) =>
            <Td key={index} field={field} element={element}/>
        )}
      </tr>);
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
        element.content.substr(0, 100)
      )
    }

    return (element[field.name]);
  }

}

