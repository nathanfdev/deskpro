import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import classNames from 'classnames';
import jQuery from 'jquery';

export class TableView extends Component {
  static propTypes = {
    children: PropTypes.any.isRequired
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


export class TableHeader extends Component {
  static propTypes = {
    children: PropTypes.any.isRequired
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
    label: PropTypes.string.isRequired,
    value: PropTypes.string.isRequired,
    order: PropTypes.string,
    className: PropTypes.string,
    isShown: PropTypes.any,
    sortTable: PropTypes.func
  };

  handleClick(param, event) {
    event.preventDefault();
    event.stopPropagation();
    const { sortTable, order } = this.props;
    if (sortTable) {
      const newOrder = order === constants.ORDER_ASC ? constants.ORDER_DESC : constants.ORDER_ASC;
      sortTable(param, newOrder);
    }
  }

  renderCaret() {
    const { order } = this.props;
    if (order) {
      var classes = classNames('fa', {
        'fa-caret-down': order === constants.ORDER_DESC,
        'fa-caret-up': order === constants.ORDER_ASC
      });
      return (
        <span className="sort-direction"><i className={classes}></i></span>
      );
    }
  }

  render() {
    const { value, label, className } = this.props;
    return (
      <th className={className} onClick={this.handleClick.bind(this, value)}>
        {label}
        { this.renderCaret() }
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
    className: PropTypes.string,
    children: PropTypes.any
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
    person: PropTypes.object.isRequired,
    email: PropTypes.string
  };

  render() {
    const { person, email } = this.props;
    return (
      <div className="user">
        <span className="dpw--avatar-face" style={{backgroundImage: 'url(../img/avatars/avatar1.png)'}}></span>
        <span className="agent-name">{person.get('name')} {email}</span>
      </div>
    );
  }
}

export class IdContainer extends Component {

  static propTypes = {
    id: PropTypes.number.isRequired
  };

  render() {
    const { id } = this.props;
    return (
      <span className="dpw--item-id">#{id}</span>
    );
  }
}

export class TableCheckbox extends Component {
  static propTypes = {
    onClick: PropTypes.func,
    selected: PropTypes.bool
  };

  render() {
    const { selected, onClick } = this.props;
    return (
      <input type="checkbox" checked={selected} className="dpw--checkbox" onClick={onClick} />
    );
  }
}