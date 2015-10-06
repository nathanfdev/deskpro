import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import classNames from 'classnames';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';

export class TableView extends Component {

  static propTypes = {
    children: PropTypes.array.isRequired
  };

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

  static propTypes = {
    children: PropTypes.array.isRequired
  };

  render() {
    return (
      <thead>
      {this.props.children}
      </thead>
    );
  }

}

export class Th extends React.Component {

  static propTypes = {
    field: PropTypes.object.isRequired,
    sortTable: PropTypes.func.isRequired
  };

  handleClick(param, event) {
    event.preventDefault();
    event.stopPropagation();
    const {sortTable, field} = this.props;
    const order = field.order === constants.ORDER_ASC ? constants.ORDER_DESC : constants.ORDER_ASC;
    sortTable(param, order);
    this.setState({'order': order});
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
}

export class TableBody extends React.Component {

  static propTypes = {
    children: PropTypes.array.isRequired
  };

  render() {
    return (
      <tbody>
      {this.props.children}
      </tbody>
    );
  }
}

export class Row extends React.Component {

  static propTypes = {
    children: PropTypes.array.isRequired
  };

  render() {
    return (
      <tr className="single-row">
        {this.props.children}
      </tr>);
  }
}

@injectIntl
export class Td extends React.Component {

  static propTypes = {
    intl: intlShape.isRequired,
    element: PropTypes.object.isRequired,
    field: PropTypes.object.isRequired
  };

  renderTdContent(element, field) {
    if (field.name === 'id') {
      return (
        <span className="dpw--item-id">#{element.id}</span>
      );
    } else if (field.name === 'author_name') {
      return (
        <div className="user">
          <span className="dpw--avatar-face" style={{backgroundImage: 'url(../img/avatars/avatar1.png)'}}></span>
          <span className="agent-name">{element.author_name}</span>
        </div>
      );
    } else if (field.name === 'title') {
      return (
        <a href="#">{element.title}</a>
      );
    } else if (field.name === 'content') {
      return (
        element.content.substr(0, 100)
      );
    } else if (field.name === 'date_created') {
      return (
        <FormattedRelative value={element.date_created}/>
      );
    } else if (field.name === 'date_published') {
      return (
        <FormattedRelative value={element.date_published}/>
      );
    }

    return (element[field.name]);
  }

  render() {
    const { element, field } = this.props;

    return (
      <td className={field.className}>
        {this.renderTdContent(element, field)}
      </td>
    );
  }

}
