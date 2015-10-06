import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import classNames from 'classnames';

export class TableView extends Component {

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


export class TableHeader extends Component {

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

export class Th extends Component {

  static propTypes = {
    field: PropTypes.object.isRequired,
    sortable: PropTypes.bool.isRequired,
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
    const { field, sortable } = this.props;
    const classes = classNames(field.className, {'sortable': sortable});
    const callback = sortable ? this.handleClick.bind(this, field.name) : ()=> {
    };
    return (
      <th className={classes} onClick={callback}>
        {field.label}
        {this.renderCaret(field)}
      </th>
    );
  }
}

export class TableBody extends Component {

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

export class Row extends Component {

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

export class Td extends Component {

  static propTypes = {
    className: PropTypes.string
  };

  render() {
    const { className } = this.props;

    return (
      <td className={className}>
        {this.props.children}
      </td>
    );
  }

}

export class PersonInTable extends Component {

  static propTypes = {
    person: PropTypes.object.isRequired
  };

  render() {
    const { person } = this.props;
    return (
      <div className="user">
        <span className="dpw--avatar-face" style={{backgroundImage: 'url(../img/avatars/avatar1.png)'}}></span>
        <span className="agent-name">{person.name}</span>
      </div>
    );
  }
}

export class IdContainer extends Component {

  static propTypes = {
    id: PropTypes.string.isRequired
  };

  render() {
    const { id } = this.props;
    return (
      <span className="dpw--item-id">#{id}</span>
    );
  }
}
