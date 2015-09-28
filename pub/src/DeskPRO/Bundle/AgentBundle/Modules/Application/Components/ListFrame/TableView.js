import React from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import classNames from 'classnames';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';

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


export class TableHeader extends React.Component {

  render() {
    const {tableViewFields, sortTable} = this.props;
    let filtered = tableViewFields.filter(function (field) {
      return field.status !== constants.FIELD_HIDDEN
    });
    filtered.sort(function (a, b) {
      return a.priority - b.priority
    });
    return (
      <thead>
      <tr>
        {filtered.map((field, index) =>
            <Th key={index} field={field} sortTable={sortTable}/>
        )}
      </tr>
      </thead>
    );
  }

}

export class Th extends React.Component {

  render() {
    const { field } = this.props;
    var classes = classNames('sortable', field.className);
    return (
      <th className={classes}
          onClick={this.handleClick.bind(this, field.name)}>
        {field.label}
        {this.renderCaret(field)}
      </th>
    );
  }

  renderCaret(field) {
    if (field.order) {
      var classes = classNames('fa', {
        'fa-caret-down': field.order === constants.ORDER_DESC,
        'fa-caret-up': field.order === constants.ORDER_ASC
      });
      return (
        <span className="sort-direction"><i className={classes}></i></span>
      );
    }
  }

  handleClick(param, event) {
    event.preventDefault();
    event.stopPropagation();
    const {sortTable, field} = this.props;
    let order = field.order === constants.ORDER_ASC ? constants.ORDER_DESC : constants.ORDER_ASC;
    sortTable(param, order);
    this.setState({'order': order});
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

@injectIntl
export class Td extends React.Component {

  static propTypes = {
    intl: intlShape.isRequired,
  };

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
    } else if (field.name === 'date_created') {
      return (
        <FormattedRelative value={element.date_created} />
      );
    } else if (field.name === 'date_published') {
      return (
        <FormattedRelative value={element.date_published} />
      );
    }

    return (element[field.name]);
  }
}
